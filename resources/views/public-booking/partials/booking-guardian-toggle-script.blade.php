<script>
(function() {
    function publicBookingParsedDobYmd(form) {
        form = form || document.getElementById('patient-details-form');
        if (!form) return null;
        var h = form.querySelector('input[type="hidden"][name="date_of_birth"]');
        if (h && h.value && /^\d{4}-\d{2}-\d{2}$/.test(h.value.trim())) {
            return h.value.trim();
        }
        var el = document.getElementById('date_of_birth');
        if (!el || !el.value) return null;
        var v = el.value.trim();
        if (/^\d{4}-\d{2}-\d{2}$/.test(v)) {
            return v;
        }
        var m = v.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
        if (m) return m[3] + '-' + m[2] + '-' + m[1];
        return null;
    }
    function publicBookingAgeFromYmd(ymd) {
        if (!ymd) return null;
        var p = ymd.split('-');
        if (p.length !== 3) return null;
        var y = parseInt(p[0], 10), mo = parseInt(p[1], 10) - 1, d = parseInt(p[2], 10);
        var bd = new Date(y, mo, d);
        if (isNaN(bd.getTime())) return null;
        var today = new Date();
        var age = today.getFullYear() - bd.getFullYear();
        var monDiff = today.getMonth() - bd.getMonth();
        if (monDiff < 0 || (monDiff === 0 && today.getDate() < bd.getDate())) age--;
        return age;
    }
    window.publicBookingToggleGuardian = function() {
        var form = document.getElementById('patient-details-form');
        var wrap = document.getElementById('public-booking-guardian-wrap');
        if (!form || !wrap) return;
        var ymd = publicBookingParsedDobYmd(form);
        var session = form.getAttribute('data-session-dob-ymd');
        if (!ymd && session && /^\d{4}-\d{2}-\d{2}$/.test(session)) ymd = session;
        var age = publicBookingAgeFromYmd(ymd);
        var minor = age !== null && age < 18;
        wrap.style.display = minor ? 'block' : 'none';
        wrap.querySelectorAll('.public-guardian-req').forEach(function(s) { s.style.display = minor ? 'inline' : 'none'; });
        var gn = document.getElementById('guardian_name');
        var gp = document.getElementById('guardian_phone');
        if (gn) gn.required = minor;
        if (gp) gp.required = minor;
    };
    function bind() {
        var form = document.getElementById('patient-details-form');
        if (form) {
            form.addEventListener('change', function(e) {
                if (e.target && e.target.name === 'date_of_birth' && window.publicBookingToggleGuardian) {
                    window.publicBookingToggleGuardian();
                }
            });
        }
        var dobEl = document.getElementById('date_of_birth');
        if (dobEl) {
            dobEl.addEventListener('input', function() {
                if (window.publicBookingToggleGuardian) window.publicBookingToggleGuardian();
            });
        }
        setTimeout(function() { if (window.publicBookingToggleGuardian) window.publicBookingToggleGuardian(); }, 200);
        setTimeout(function() { if (window.publicBookingToggleGuardian) window.publicBookingToggleGuardian(); }, 600);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bind);
    } else {
        bind();
    }
})();
</script>
