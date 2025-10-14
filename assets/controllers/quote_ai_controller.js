import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['modal', 'input', 'preview', 'errors'];
    static values = { endpointPreview: String, endpointCreate: String };

    open(event) {
        event?.preventDefault();
        // console.log('🟢 quote-ai#open triggered');
        this.modalTarget.classList.remove('hidden');
    }

    close() {
        this.modalTarget.classList.add('hidden');
        this.reset();
    }

    reset() {
        if (this.hasInputTarget) this.inputTarget.value = '';
        if (this.hasPreviewTarget) this.previewTarget.textContent = '';
        if (this.hasErrorsTarget) { this.errorsTarget.textContent = ''; this.errorsTarget.classList.add('hidden'); }
    }

    async preview() { await this._submit(this.endpointPreviewValue, (json) => this._showPreview(json)); }
    async create() {
        await this._submit(this.endpointCreateValue, (json) => {
            if (json.ok && json.redirect) window.location.href = json.redirect;
            else this._showError(json.detail || 'Unable to create quote');
        });
    }

    async _submit(url, onOk) {
        this._showError(null);
        const msg = (this.inputTarget?.value || '').trim();
        const form = new FormData();
        form.append('message', msg);

        const resp = await fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: form });
        let json; try { json = await resp.json(); } catch { return this._showError('Invalid server response'); }
        if (!resp.ok || json.error) return this._showError(json.detail || json.error || 'Request failed');
        onOk(json);
    }

    _showPreview(json) {
        const pretty = JSON.stringify({ parsed: json.parsed, resolved: json.resolved }, null, 2);
        this.previewTarget.textContent = pretty;
    }

    _showError(msg) {
        if (!msg) { this.errorsTarget.classList.add('hidden'); this.errorsTarget.textContent = ''; return; }
        this.errorsTarget.textContent = typeof msg === 'string' ? msg : JSON.stringify(msg);
        this.errorsTarget.classList.remove('hidden');
    }
}
