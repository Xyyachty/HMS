/**
 * HMS Hotel Template Builder client
 * Role-scoped edit, manual save (Ctrl+S / Save Draft), autosave timer, sync, version restore.
 */
(function (window) {
  'use strict';

  function csrf() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta && meta.content) return meta.content;
    const m = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);
    return m ? decodeURIComponent(m[1]) : '';
  }

  /** Order-independent serialization so key comparisons are stable. */
  function stableStringify(value) {
    if (value === null || typeof value !== 'object') return JSON.stringify(value);
    if (Array.isArray(value)) return '[' + value.map(stableStringify).join(',') + ']';
    return '{' + Object.keys(value).sort().map(function (k) {
      return JSON.stringify(k) + ':' + stableStringify(value[k]);
    }).join(',') + '}';
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

  function HotelBuilder(options) {
    this.role = options.role;
    this.canEdit = !!options.canEdit;
    this.mode = options.mode || (this.canEdit ? 'build' : 'preview');
    this.state = options.initial || {};
    this.syncVersion = options.initial?.sync_version || 0;
    this.routes = options.routes || {};
    this.onChange = options.onChange || function () {};
    this.onToast = options.onToast || function () {};
    this._dirty = false;
    this._syncTimer = null;
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
    this._dirty = true;
    this.onChange({ type: 'dirty', dirty: true });
  };

  HotelBuilder.prototype.isDirty = function () {
    return !!this._dirty;
  };

  HotelBuilder.prototype._headers = function () {
    return {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': csrf(),
    };
  };

  /**
   * Keys whose current value differs from the last merged state the server
   * sent — i.e. edits this member actually made, as opposed to teammates'
   * work that merely passed through this browser.
   */
  HotelBuilder.prototype.locallyChangedKeys = function () {
    const current = window.templateCustomizations || this.state.customizations || {};
    const baseline = this.mergedBaseline || {};
    const changed = {};
    Object.keys(current).forEach(function (key) {
      if (stableStringify(current[key]) !== stableStringify(baseline[key])) {
        changed[key] = current[key];
      }
    });
    // Reset design deletes keys outright, so they are gone from `current` and
    // the loop above cannot see them. Left unreported, the next save posted the
    // server's old value straight back and the reset appeared to undo itself.
    Object.keys(baseline).forEach(function (key) {
      if (!Object.prototype.hasOwnProperty.call(current, key)) {
        changed[key] = REMOVED;
      }
    });
    return changed;
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
    };
  };

  HotelBuilder.prototype.applyServerState = function (tpl, keepLocal) {
    if (!tpl) return;
    this.state = Object.assign({}, this.state, tpl);
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
    this.onChange({ type: 'state', template: Object.assign({}, tpl, { customizations: view }) });
  };

  HotelBuilder.prototype.autosave = async function () {
    if (!this.canEdit || this.mode !== 'build' || !this._dirty) return;
    if (this._autosaving) return;
    this._autosaving = true;
    try {
      if (typeof window.postToTemplate === 'function') {
        window.postToTemplate({ type: 'request-customizations' });
        await new Promise(function (r) { setTimeout(r, 120); });
      }
      if (window.templateCustomizations) {
        this.state.customizations = window.templateCustomizations;
      }
      const res = await fetch(this.routes.autosave, {
        method: 'POST',
        credentials: 'same-origin',
        headers: this._headers(),
        body: JSON.stringify(this.payloadBody()),
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.error || 'Autosave failed');
      this._dirty = false;
      // Keep anything typed while the request was in flight.
      this.applyServerState(data.template, this.locallyChangedKeys());
      this.onChange({ type: 'dirty', dirty: false });
      this.onChange({ type: 'autosaved' });
    } catch (e) {
      // silent — stays dirty, next timer tick retries
    } finally {
      this._autosaving = false;
    }
  };

  HotelBuilder.prototype.startAutoSave = function (ms) {
    const self = this;
    clearInterval(this._autoSaveTimer);
    this._autoSaveTimer = setInterval(function () { self.autosave(); }, ms || 7000);
  };

  HotelBuilder.prototype.save = async function (publish) {
    if (!this.canEdit) {
      this.onToast('You cannot edit this template');
      return;
    }
    try {
      if (typeof window.postToTemplate === 'function') {
        window.postToTemplate({ type: 'request-customizations' });
        await new Promise(function (r) { setTimeout(r, 120); });
      }
      if (window.templateCustomizations) {
        this.state.customizations = window.templateCustomizations;
      }
      const body = Object.assign(this.payloadBody(), {
        publish: !!publish,
        snapshot: true,
        label: publish ? 'Published' : 'Manual save',
      });
      const res = await fetch(this.routes.save, {
        method: 'POST',
        credentials: 'same-origin',
        headers: this._headers(),
        body: JSON.stringify(body),
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.error || 'Save failed');
      this._dirty = false;
      // Keep anything typed while the request was in flight.
      this.applyServerState(data.template, this.locallyChangedKeys());
      this.onChange({ type: 'dirty', dirty: false });
      this.onToast(publish ? 'Published — team can see updates' : 'Draft saved');
      return data.template;
    } catch (e) {
      this.onToast(e.message || 'Save failed');
      throw e;
    }
  };

  HotelBuilder.prototype.sync = async function () {
    try {
      const res = await fetch(this.routes.sync, {
        credentials: 'same-origin',
        headers: this._headers(),
      });
      const data = await res.json();
      if (!res.ok) return;
      const next = data.sync_version || 0;
      if (next && next !== this.syncVersion) {
        // An active editor still receives every teammate's change; only the
        // keys they are actively editing are held back from being overwritten.
        const pending = (this.canEdit && this._dirty && this.mode === 'build')
          ? this.locallyChangedKeys()
          : null;
        this.applyServerState(data, pending);
        this.onToast('Team update synced');
      } else if (typeof data.can_edit === 'boolean' && data.can_edit !== this.canEdit) {
        window.location.reload();
      }
    } catch (e) { /* ignore */ }
  };

  HotelBuilder.prototype.startPolling = function (ms) {
    const self = this;
    clearInterval(this._syncTimer);
    this._syncTimer = setInterval(function () { self.sync(); }, ms || 4000);
    this.sync();
  };

  HotelBuilder.prototype.loadVersions = async function () {
    const res = await fetch(this.routes.versions, {
      credentials: 'same-origin',
      headers: this._headers(),
    });
    return res.json();
  };

  HotelBuilder.prototype.restoreVersion = async function (version) {
    if (!this.canEdit) {
      this.onToast('Cannot restore without edit permission');
      return;
    }
    const url = this.routes.restore.replace('__VERSION__', version);
    const res = await fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: this._headers(),
      body: '{}',
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Restore failed');
    this._dirty = false;
    this.applyServerState(data.template);
    this.onToast('Restored version ' + version);
    return data.template;
  };

  /** Layout helpers — no drag and drop */
  HotelBuilder.prototype.addComponent = function (componentId) {
    if (!this.canEdit || this.mode !== 'build') return;
    const layout = Array.isArray(this.state.layout) ? this.state.layout.slice() : [];
    layout.push({ id: componentId, visible: true, key: componentId + '_' + Date.now() });
    this.state.layout = layout;
    this.markDirty();
    this.onChange({ type: 'layout', layout: layout });
  };

  HotelBuilder.prototype.removeComponent = function (index) {
    if (!this.canEdit || this.mode !== 'build') return;
    const layout = Array.isArray(this.state.layout) ? this.state.layout.slice() : [];
    layout.splice(index, 1);
    this.state.layout = layout;
    this.markDirty();
    this.onChange({ type: 'layout', layout: layout });
  };

  HotelBuilder.prototype.toggleComponent = function (index) {
    if (!this.canEdit || this.mode !== 'build') return;
    const layout = Array.isArray(this.state.layout) ? this.state.layout.slice() : [];
    if (!layout[index]) return;
    layout[index] = Object.assign({}, layout[index], { visible: !layout[index].visible });
    this.state.layout = layout;
    this.markDirty();
    this.onChange({ type: 'layout', layout: layout });
  };

  HotelBuilder.prototype.moveComponent = function (index, direction) {
    if (!this.canEdit || this.mode !== 'build') return;
    const layout = Array.isArray(this.state.layout) ? this.state.layout.slice() : [];
    const target = index + direction;
    if (target < 0 || target >= layout.length) return;
    const tmp = layout[index];
    layout[index] = layout[target];
    layout[target] = tmp;
    this.state.layout = layout;
    this.markDirty();
    this.onChange({ type: 'layout', layout: layout });
  };

  window.HMSHotelBuilder = HotelBuilder;
})(window);
