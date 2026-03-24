{{-- Keeps "Reason for booking" (#notes) in sessionStorage for 60s (refreshed on each edit) so a quick refresh does not lose text. Cleared on submit. --}}
<script>
(function() {
    var KEY = 'thanksdoc_pb_reason_booking:' + (window.location.pathname || '/');
    var TTL_MS = 60000;
    var notesEl = document.getElementById('notes');
    if (!notesEl || notesEl.name !== 'notes') return;

    function loadDraft() {
        try {
            var raw = sessionStorage.getItem(KEY);
            if (!raw) return;
            var o = JSON.parse(raw);
            if (!o || typeof o.v !== 'string' || typeof o.t !== 'number') {
                sessionStorage.removeItem(KEY);
                return;
            }
            if (Date.now() - o.t > TTL_MS) {
                sessionStorage.removeItem(KEY);
                return;
            }
            if (!notesEl.value.trim()) {
                notesEl.value = o.v;
            }
        } catch (e) {
            sessionStorage.removeItem(KEY);
        }
    }

    function saveDraft() {
        try {
            var v = notesEl.value;
            if (v.trim() === '') {
                sessionStorage.removeItem(KEY);
                return;
            }
            sessionStorage.setItem(KEY, JSON.stringify({ t: Date.now(), v: v }));
        } catch (e) {}
    }

    var saveTimer;
    notesEl.addEventListener('input', function() {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(saveDraft, 200);
    });
    notesEl.addEventListener('blur', saveDraft);

    loadDraft();

    var form = notesEl.form;
    if (form) {
        form.addEventListener('submit', function() {
            try { sessionStorage.removeItem(KEY); } catch (e) {}
        });
    }
})();
</script>
