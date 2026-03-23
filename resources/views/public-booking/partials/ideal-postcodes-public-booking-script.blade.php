<script>
(function initIdealPostcodesPublicBooking() {
    const apiKey = @json(\App\Models\Setting::get('ideal_postcodes_api_key') ?: config('services.ideal_postcodes.api_key'));
    const input = document.getElementById('ideal_postcodes_finder');
    const notice = document.getElementById('ideal_postcodes_notice');
    if (!input || !notice) return;

    function hideNotice() {
        notice.style.display = 'none';
        notice.textContent = '';
    }

    function showNotice(msg) {
        notice.style.display = 'block';
        notice.innerHTML = '<i class="fas fa-info-circle me-1"></i>' + msg;
    }

    function getAF() {
        if (typeof AddressFinder !== 'undefined' && AddressFinder) return AddressFinder;
        if (window.IdealPostcodes && window.IdealPostcodes.AddressFinder) return window.IdealPostcodes.AddressFinder;
        return null;
    }

    function ensureAFScript() {
        if (getAF()) return;
        if (document.querySelector('script[data-ideal-postcodes-af="1"]')) return;
        const s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/@ideal-postcodes/address-finder-bundled@5/dist/address-finder.js';
        s.async = true;
        s.setAttribute('data-ideal-postcodes-af', '1');
        document.head.appendChild(s);
    }

    function waitForAF(timeoutMs) {
        return new Promise(function(resolve, reject) {
            const start = Date.now();
            ensureAFScript();
            (function tick() {
                const AF = getAF();
                if (AF && typeof AF.setup === 'function') return resolve(AF);
                if (Date.now() - start > timeoutMs) return reject(new Error('Ideal Postcodes AddressFinder load timeout'));
                setTimeout(tick, 50);
            })();
        });
    }

    if (!apiKey) {
        showNotice('Address lookup is unavailable (missing API key). Please enter your address manually.');
        return;
    }
    hideNotice();
    waitForAF(8000)
        .then(function(AF) {
            AF.setup({
                apiKey: apiKey,
                inputField: input,
                outputFields: {
                    line_1: '#address',
                    line_2: '#address_line_2',
                    post_town: '#city',
                    county: '#state',
                    postcode: '#postal_code',
                },
                onCheckFailed: function() {
                    showNotice('Address lookup is unavailable right now. Please enter your address manually.');
                },
            });
        })
        .catch(function(e) {
            console.error('Ideal Postcodes load/init failed:', e);
            showNotice('Address lookup failed to load. Please enter your address manually.');
        });
})();
</script>
