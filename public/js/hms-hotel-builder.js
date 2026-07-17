/**
 * HMS Hotel Template Builder client
 * Role-scoped edit, autosave, sync, version restore. Inline canvas editing (no sidebar drag-drop).
 */
(function (window) {
  'use strict';

  function csrf() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta && meta.content) return meta.content;
    const m = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);
    return m ? decodeURIComponent(m[1]) : '';
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
    this._autosaveTimer = null;
    this._dirty = false;
    this._syncTimer = null;
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
    clearTimeout(this._autosaveTimer);
    const self = this;
    this._autosaveTimer = setTimeout(function () { self.autosave(); }, 1800);
  };

  HotelBuilder.prototype._headers = function () {
    return {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': csrf(),
    };
  };

  HotelBuilder.prototype.payloadBody = function () {
    return {
      customizations: this.state.customizations || window.templateCustomizations || {},
      layout: this.state.layout || [],
      selected_template: this.state.selected_template || null,
    };
  };

  HotelBuilder.prototype.applyServerState = function (tpl) {
    if (!tpl) return;
    this.state = Object.assign({}, this.state, tpl);
    this.syncVersion = tpl.sync_version || this.syncVersion;
    if (tpl.can_edit != null) this.canEdit = !!tpl.can_edit;
    window.templateCustomizations = tpl.customizations || {};
    this.onChange({ type: 'state', template: tpl });
  };

  HotelBuilder.prototype.autosave = async function () {
    if (!this.canEdit || !this._dirty) return;
    try {
      const res = await fetch(this.routes.autosave, {
        method: 'POST',
        credentials: 'same-origin',
        headers: this._headers(),
        body: JSON.stringify(this.payloadBody()),
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.error || 'Autosave failed');
      this._dirty = false;
      this.applyServerState(data.template);
      this.onToast('Auto-saved');
    } catch (e) {
      console.error(e);
    }
  };

  HotelBuilder.prototype.save = async function (publish) {
    if (!this.canEdit) {
      this.onToast('You cannot edit this template');
      return;
    }
    try {
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
      this.applyServerState(data.template);
      this.onToast(publish ? 'Published — team can see updates' : 'Saved — team synced');
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
        // Don't clobber local dirty buffer for the active editor
        if (this.canEdit && this._dirty && this.mode === 'build') {
          return;
        }
        this.applyServerState(data);
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
