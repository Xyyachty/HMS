/**
 * HMS Hotel Template Builder client
 * Role-scoped edit, manual save (Ctrl+S / Save Draft), autosave timer, sync, version restore.
 *
 * Every write and every sync goes through one queue, so they never overlap:
 * two requests in flight at once used to come back in either order and the
 * older answer could replace the newer state. Each write carries the revision
 * of this role's template it was based on. When a teammate saved in between,
 * the server answers 409 with the current template, and the edits here are
 * rebased on it. The student is asked only when both of them changed the same
 * thing.
 */
(function (window) {
  'use strict';

  /**
   * The CSRF token to send. The XSRF-TOKEN cookie is preferred over the <meta>
   * tag: Laravel refreshes the cookie on every response, while the tag is
   * frozen at page load. A student who signs in again in another tab after
   * their session lapsed gets a new token in the cookie, and saving resumes
   * here without a reload that would throw away their unsaved edits.
   */
  function csrfHeaders() {
    const m = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);
    if (m) return { 'X-XSRF-TOKEN': decodeURIComponent(m[1]) };
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta && meta.content ? { 'X-CSRF-TOKEN': meta.content } : {};
  }

  /** Order-independent serialization so key comparisons are stable. */
  function stableStringify(value) {
    if (value === null || typeof value !== 'object') return JSON.stringify(value);
    if (Array.isArray(value)) return '[' + value.map(stableStringify).join(',') + ']';
    return '{' + Object.keys(value).sort().map(function (k) {
      return JSON.stringify(k) + ':' + stableStringify(value[k]);
    }).join(',') + '}';
  }

  function sameValue(a, b) {
    return stableStringify(a) === stableStringify(b);
  }

  function clone(value) {
    return value === undefined ? undefined : JSON.parse(JSON.stringify(value));
  }

  /**
   * Marks a key the member deleted locally (Reset design). A deleted key is
   * simply absent from the customizations object, which is indistinguishable
   * from "never set" — so removals need an explicit marker to survive the
   * save/sync merge instead of being silently refilled from the server.
   * Never leaves the browser: applyLocalOverlay turns it back into a deletion.
   */
  const REMOVED = Object.freeze({ __hmsRemoved: true });

  /** base + local edits, where a REMOVED marker deletes the key outright. */
  function applyLocalOverlay(base, overlay) {
    const out = Object.assign({}, base);
    Object.keys(overlay || {}).forEach(function (key) {
      if (overlay[key] === REMOVED) delete out[key];
      else out[key] = overlay[key];
    });
    return out;
  }

  /** Keys whose value in `current` differs from `reference`, REMOVED for deletions. */
  function diffKeys(current, reference) {
    const changed = {};
    Object.keys(current || {}).forEach(function (key) {
      if (!sameValue(current[key], (reference || {})[key])) changed[key] = current[key];
    });
    Object.keys(reference || {}).forEach(function (key) {
      if (!Object.prototype.hasOwnProperty.call(current || {}, key)) changed[key] = REMOVED;
    });
    return changed;
  }

  /** What a customization key is, in words a student recognises. */
  const KEY_NAMES = {
    __menus: 'the menu',
    __rooms: 'the rooms',
    __siteColors: 'the site colours',
    __hotelInfo: 'the hotel information',
    __cardImages: 'the card pictures',
    __heroSlides: 'the hero photos',
    __navLinks: 'the menu links',
    __brandName: 'the hotel name',
    __socialLinks: 'the social links',
    __typography: 'the fonts',
    __roomCardStyle: 'the room card style',
    __menuCardStyle: 'the menu card style',
    __userElements: 'the added elements',
  };

  function describeKeys(keys) {
    const named = [];
    let elements = 0;
    keys.forEach(function (key) {
      if (KEY_NAMES[key]) named.push(KEY_NAMES[key]);
      else elements++;
    });
    if (elements) named.push(elements === 1 ? 'one element on the page' : elements + ' elements on the page');
    return named.join(', ');
  }

  /** The body of a response, or {} when it is not JSON (a proxy's HTML error page). */
  async function readJson(res) {
    try {
      return await res.json();
    } catch (e) {
      return {};
    }
  }

  /** Why a save failed, as something the student can act on. */
  function failureMessage(status, data) {
    const own = data && (data.error || data.message);
    if (status === 0) return 'You seem to be offline. Your edits are kept on this screen and will save when the connection is back.';
    if (status === 401 || status === 419) return 'Your session has expired. Sign in again in another tab, then come back — your edits here are kept and saving will resume.';
    if (status === 403) return own || 'You can no longer edit this page. Reload to see your current role.';
    if (status === 413) return 'That change is too large to save — usually a very large picture. Use a smaller image and try again.';
    if (status === 422) {
      const first = data && data.errors && Object.values(data.errors)[0];
      return (Array.isArray(first) && first[0]) || own || 'The server did not accept these changes.';
    }
    return own || 'The server could not save your changes. They are kept on this screen and will be retried.';
  }

  function saveError(status, data) {
    const err = new Error(failureMessage(status, data));
    err.status = status;
    return err;
  }

  function HotelBuilder(options) {
    this.role = options.role;
    this.canEdit = !!options.canEdit;
    this.mode = options.mode || (this.canEdit ? 'build' : 'preview');
    this.state = options.initial || {};
    this.syncVersion = options.initial?.sync_version || 0;
    this.routes = options.routes || {};
    this.onChange = options.onChange || function () {};
    this.onToast = options.onToast || function () {};
    /* Asked when a teammate changed the same thing this student changed.
       Resolves true to keep this student's version, false to take the
       teammate's. The browser's confirm is the fallback. */
    this.onConflict = options.onConflict || function (summary) {
      return Promise.resolve(window.confirm(
        'A teammate saved changes to ' + summary + ' while you were editing it.\n\n'
        + 'OK keeps your version and replaces theirs. Cancel keeps theirs and drops your edits to it.'
      ));
    };
    this._dirty = false;
    // Goes up on every local edit. A save compares it before and after, so an
    // edit made while the request was in flight is not marked as saved.
    this._editSeq = 0;
    this._layoutEdited = false;
    this._syncTimer = null;
    this._queue = Promise.resolve();
    this._busy = 0;
    this._autosaveQueued = false;
    this._lastError = null;
    // Last merged team state received from the server. Anything that differs
    // from this was changed locally; anything equal to it is somebody else's
    // work we are merely displaying and must never write back.
    this.mergedBaseline = (options.initial && options.initial.customizations) || {};
  }

  HotelBuilder.prototype.setMode = function (mode) {
    if (mode === 'build' && !this.canEdit) {
      this.onToast('Preview only — you need permission to edit this role template');
      return;
    }
    this.mode = mode === 'build' ? 'build' : 'preview';
    this.onChange({ type: 'mode', mode: this.mode });
  };

  HotelBuilder.prototype.markDirty = function () {
    if (!this.canEdit || this.mode !== 'build') return;
    this._editSeq++;
    this._dirty = true;
    this.onChange({ type: 'dirty', dirty: true });
  };

  HotelBuilder.prototype.isDirty = function () {
    return !!this._dirty;
  };

  /** Whether a write is queued or in flight. */
  HotelBuilder.prototype.isSaving = function () {
    return this._busy > 0;
  };

  HotelBuilder.prototype._headers = function () {
    return Object.assign({
      'Content-Type': 'application/json',
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    }, csrfHeaders());
  };

  /** Run one write or sync after everything queued before it has finished. */
  HotelBuilder.prototype._enqueue = function (task) {
    const self = this;
    this._busy++;
    const run = function () { return task(); };
    const result = this._queue.then(run, run);
    this._queue = result.catch(function () {}).then(function () { self._busy--; });
    return result;
  };

  /**
   * Ask the page in the frame for its current customizations and wait for the
   * answer. It used to wait a fixed 120ms, and a busy page answering later
   * than that meant the save went out without the student's last edit.
   */
  HotelBuilder.prototype._collectCustomizations = function () {
    if (typeof window.postToTemplate !== 'function') return Promise.resolve();
    return new Promise(function (resolve) {
      let done = false;
      let timer = null;
      function finish() {
        if (done) return;
        done = true;
        clearTimeout(timer);
        window.removeEventListener('message', onMessage);
        resolve();
      }
      function onMessage(event) {
        const data = event.data || {};
        // The page's own listener stores the answer; let it run first.
        if (data.source === 'hms-template' && data.type === 'customizations-changed') setTimeout(finish, 0);
      }
      window.addEventListener('message', onMessage);
      timer = setTimeout(finish, 1500);
      window.postToTemplate({ type: 'request-customizations' });
    });
  };

  /**
   * Keys whose current value differs from the last merged state the server
   * sent — i.e. edits this member actually made, as opposed to teammates'
   * work that merely passed through this browser.
   */
  HotelBuilder.prototype.locallyChangedKeys = function () {
    const current = window.templateCustomizations || this.state.customizations || {};
    // Reset design deletes keys outright, so they show up as REMOVED rather
    // than being missed and the server's old value posted straight back.
    return diffKeys(current, this.mergedBaseline || {});
  };

  /**
   * Persist this role's own row plus our local edits — never the merged team
   * set. Echoing merged state lets a stale copy of a shared key overwrite a
   * teammate's newer value once the rows are merged again.
   */
  HotelBuilder.prototype.payloadBody = function () {
    const own = this.state.own_customizations;
    // Without a known own-row baseline, fall back to the current view rather
    // than posting a near-empty set that would wipe this role's saved work.
    const base = own && typeof own === 'object'
      ? applyLocalOverlay(own, this.locallyChangedKeys())
      : (window.templateCustomizations || this.state.customizations || {});
    return {
      customizations: base,
      layout: this.state.layout || [],
      selected_template: this.state.selected_template || null,
      // The revision these edits were made on top of. The server refuses the
      // save if the template has moved on since.
      base_revision: this.state.revision == null ? null : this.state.revision,
    };
  };

  HotelBuilder.prototype.applyServerState = function (tpl, keepLocal) {
    if (!tpl) return;
    const localLayout = this.state.layout;
    this.state = Object.assign({}, this.state, tpl);
    // Section order edited here and not yet saved stays as the student left it.
    if (this._layoutEdited) this.state.layout = localLayout;
    this.syncVersion = tpl.sync_version || this.syncVersion;
    if (tpl.can_edit != null) this.canEdit = !!tpl.can_edit;

    const serverMerged = tpl.customizations || {};
    this.mergedBaseline = serverMerged;

    // Re-overlay unsaved local edits so receiving teammate updates never
    // discards work in progress.
    const view = keepLocal && Object.keys(keepLocal).length
      ? applyLocalOverlay(serverMerged, keepLocal)
      : Object.assign({}, serverMerged);

    window.templateCustomizations = view;
    this.state.customizations = view;
    this.onChange({ type: 'state', template: Object.assign({}, this.state, { customizations: view }) });
  };

  /**
   * Take the server's newer template and put this student's unsaved edits
   * back on top of it.
   *
   * An edit to something no teammate touched carries over without a word. An
   * edit to something a teammate also changed is a real clash, and the student
   * chooses: keep theirs, or keep their own and replace the teammate's. Nothing
   * is overwritten without that choice.
   *
   * Resolves true when edits are left to save.
   */
  HotelBuilder.prototype._rebase = async function (tpl) {
    const before = this.mergedBaseline || {};
    const theirs = tpl.customizations || {};
    const local = this.locallyChangedKeys();

    const clashes = Object.keys(local).filter(function (key) {
      const mine = local[key] === REMOVED ? undefined : local[key];
      return !sameValue(theirs[key], before[key]) && !sameValue(theirs[key], mine);
    });

    let keep = local;
    if (clashes.length) {
      const keepMine = await this.onConflict(describeKeys(clashes), clashes);
      if (!keepMine) {
        keep = {};
        Object.keys(local).forEach(function (key) {
          if (clashes.indexOf(key) === -1) keep[key] = local[key];
        });
      }
    }

    this.applyServerState(tpl, keep);
    const left = Object.keys(keep).length > 0 || this._layoutEdited;
    if (!left) {
      this._dirty = false;
      this.onChange({ type: 'dirty', dirty: false });
    }
    return left;
  };

  /**
   * One write, from reading the page to handling the answer. Queued by the
   * callers, so it never runs beside another write or a sync.
   */
  HotelBuilder.prototype._writeNow = async function (url, extra, round) {
    // A retry after a rebase already holds the rebased state. Asking the page
    // again could get its copy from before the rebase, which still has the
    // teammate's old values, and send those back over the teammate's new ones.
    if (!round) {
      await this._collectCustomizations();
      if (window.templateCustomizations) this.state.customizations = window.templateCustomizations;
    }

    const seq = this._editSeq;
    const sent = clone(window.templateCustomizations || this.state.customizations || {});
    const body = Object.assign(this.payloadBody(), extra || {});

    let res;
    try {
      res = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: this._headers(),
        body: JSON.stringify(body),
      });
    } catch (e) {
      throw saveError(0, {});
    }
    const data = await readJson(res);

    if (res.status === 409 && data.template && (round || 0) < 3) {
      const left = await this._rebase(data.template);
      if (!left) return this.state;
      return this._writeNow(url, extra, (round || 0) + 1);
    }
    if (!res.ok || !data.template) throw saveError(res.status, data);

    // Only what changed after the request was sent is still unsaved. Keeping
    // everything that differed from the old baseline re-posted pictures the
    // server had already stored, and uploaded them again on every save.
    const typedSince = diffKeys(window.templateCustomizations || {}, sent);
    this.applyServerState(data.template, typedSince);
    if (this._editSeq === seq) {
      this._dirty = false;
      this._layoutEdited = false;
    }
    this._lastError = null;
    this.onChange({ type: 'dirty', dirty: this._dirty });
    return data.template;
  };

  HotelBuilder.prototype._reportFailure = function (err) {
    const message = err.message || 'Save failed';
    const repeated = this._lastError === message;
    this._lastError = message;
    this.onChange({ type: 'save-error', message: message, status: err.status || 0, repeated: repeated });
  };

  /**
   * Resolves true when the draft is saved (or nothing needed saving), false
   * when it could not be. Failures are reported through onChange rather than
   * swallowed: the edits stay dirty and the next tick retries.
   */
  HotelBuilder.prototype.autosave = function () {
    if (!this.canEdit || this.mode !== 'build' || !this._dirty) return Promise.resolve(true);
    if (this._autosaveQueued) return this._autosavePromise;
    const self = this;
    this._autosaveQueued = true;
    this._autosavePromise = this._enqueue(async function () {
      self._autosaveQueued = false;
      // A save queued ahead of this one may already have written everything.
      if (!self._dirty) return true;
      // Say so before the request goes out: an autosave that is only announced
      // once it lands leaves the student watching a stale "unsaved" line.
      self.onChange({ type: 'saving' });
      try {
        await self._writeNow(self.routes.autosave, {}, 0);
        self.onChange({ type: 'autosaved' });
        return true;
      } catch (e) {
        self._reportFailure(e);
        return false;
      }
    });
    return this._autosavePromise;
  };

  /** Save whatever is unsaved now, and wait for it. Rejects with the reason if it fails. */
  HotelBuilder.prototype.flush = function () {
    if (!this.canEdit) return Promise.resolve(true);
    const self = this;
    return this._enqueue(async function () {
      if (!self._dirty) return true;
      self.onChange({ type: 'saving' });
      try {
        await self._writeNow(self.routes.autosave, {}, 0);
        return true;
      } catch (e) {
        self._reportFailure(e);
        throw e;
      }
    });
  };

  HotelBuilder.prototype.startAutoSave = function (ms) {
    const self = this;
    clearInterval(this._autoSaveTimer);
    this._autoSaveTimer = setInterval(function () { self.autosave(); }, ms || 7000);
  };

  HotelBuilder.prototype.save = function (publish) {
    if (!this.canEdit) {
      this.onToast('You cannot edit this template');
      return Promise.reject(new Error('You cannot edit this template'));
    }
    const self = this;
    this.onChange({ type: 'saving' });
    return this._enqueue(async function () {
      try {
        const tpl = await self._writeNow(self.routes.save, {
          publish: !!publish,
          snapshot: true,
          label: publish ? 'Published' : 'Manual save',
        }, 0);
        self.onToast(publish ? 'Published — team can see updates' : 'Draft saved');
        return tpl;
      } catch (e) {
        self._reportFailure(e);
        self.onToast(e.message || 'Save failed');
        throw e;
      }
    });
  };

  HotelBuilder.prototype.sync = function () {
    const self = this;
    return this._enqueue(async function () {
      try {
        const res = await fetch(self.routes.sync, {
          credentials: 'same-origin',
          headers: self._headers(),
        });
        const data = await readJson(res);
        if (!res.ok) return;
        const next = data.sync_version || 0;
        if (next && next !== self.syncVersion) {
          // An active editor still receives every teammate's change; only the
          // keys they are actively editing are held back, and a key both of
          // them changed is put to the student rather than decided silently.
          if (self.canEdit && self._dirty && self.mode === 'build') {
            await self._rebase(data);
          } else {
            self.applyServerState(data, null);
          }
          self.onToast('Team update synced');
        } else if (typeof data.can_edit === 'boolean' && data.can_edit !== self.canEdit) {
          window.location.reload();
        }
      } catch (e) { /* offline: the next poll tries again */ }
    });
  };

  HotelBuilder.prototype.startPolling = function (ms) {
    const self = this;
    clearInterval(this._syncTimer);
    // A backgrounded tab polls nothing; the page syncs on coming back to it.
    this._syncTimer = setInterval(function () { if (!document.hidden) self.sync(); }, ms || 4000);
    this.sync();
  };

  HotelBuilder.prototype.loadVersions = async function () {
    const res = await fetch(this.routes.versions, {
      credentials: 'same-origin',
      headers: this._headers(),
    });
    return res.json();
  };

  HotelBuilder.prototype.restoreVersion = function (version) {
    if (!this.canEdit) {
      this.onToast('Cannot restore without edit permission');
      return Promise.resolve();
    }
    const self = this;
    const url = this.routes.restore.replace('__VERSION__', version);
    return this._enqueue(async function () {
      const res = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: self._headers(),
        body: '{}',
      });
      const data = await readJson(res);
      if (!res.ok || !data.template) throw saveError(res.status, data);
      self._dirty = false;
      self._layoutEdited = false;
      self.applyServerState(data.template);
      self.onChange({ type: 'dirty', dirty: false });
      self.onToast('Restored version ' + version);
      return data.template;
    });
  };

  /** Layout helpers — no drag and drop */
  HotelBuilder.prototype._setLayout = function (layout) {
    this.state.layout = layout;
    this._layoutEdited = true;
    this.markDirty();
    this.onChange({ type: 'layout', layout: layout });
  };

  HotelBuilder.prototype.addComponent = function (componentId) {
    if (!this.canEdit || this.mode !== 'build') return;
    const layout = Array.isArray(this.state.layout) ? this.state.layout.slice() : [];
    layout.push({ id: componentId, visible: true, key: componentId + '_' + Date.now() });
    this._setLayout(layout);
  };

  HotelBuilder.prototype.removeComponent = function (index) {
    if (!this.canEdit || this.mode !== 'build') return;
    const layout = Array.isArray(this.state.layout) ? this.state.layout.slice() : [];
    layout.splice(index, 1);
    this._setLayout(layout);
  };

  HotelBuilder.prototype.toggleComponent = function (index) {
    if (!this.canEdit || this.mode !== 'build') return;
    const layout = Array.isArray(this.state.layout) ? this.state.layout.slice() : [];
    if (!layout[index]) return;
    layout[index] = Object.assign({}, layout[index], { visible: !layout[index].visible });
    this._setLayout(layout);
  };

  HotelBuilder.prototype.moveComponent = function (index, direction) {
    if (!this.canEdit || this.mode !== 'build') return;
    const layout = Array.isArray(this.state.layout) ? this.state.layout.slice() : [];
    const target = index + direction;
    if (target < 0 || target >= layout.length) return;
    const tmp = layout[index];
    layout[index] = layout[target];
    layout[target] = tmp;
    this._setLayout(layout);
  };

  window.HMSHotelBuilder = HotelBuilder;
})(window);
