{{-- Philippine mobile inputs. Any <input data-ph-phone> keeps only the 10 digits
     after +63, so a pasted "0917 123 4567" or "+63 917-123-4567" becomes
     "9171234567" and an eleventh digit cannot be typed. The server repeats the
     check in App\Rules\PhilippineMobile. --}}
<script>
    window.normalizePhPhone = function (value) {
        let digits = String(value || '').replace(/\D/g, '');
        if (digits.length > 10 && digits.startsWith('63')) {
            digits = digits.slice(2);
        } else if (digits.startsWith('0')) {
            digits = digits.slice(1);
        }
        return digits.slice(0, 10);
    };

    document.addEventListener('input', function (event) {
        const input = event.target;
        if (!(input instanceof HTMLInputElement) || !input.hasAttribute('data-ph-phone')) return;
        const cleaned = window.normalizePhPhone(input.value);
        if (cleaned !== input.value) input.value = cleaned;
    });
</script>
