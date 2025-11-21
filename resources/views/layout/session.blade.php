<script>
setInterval(() => {
    fetch('/online', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
}, 14500);
</script>