/* T Solutions - Scripts */
document.addEventListener('DOMContentLoaded', function () {
    const alerts = document.querySelectorAll('.alert-dismissible');
    if (alerts.length) {
        setTimeout(function () {
            alerts.forEach(function (a) {
                const bs = bootstrap.Alert.getOrCreateInstance(a);
                if (bs) bs.close();
            });
        }, 5000);
    }
});
