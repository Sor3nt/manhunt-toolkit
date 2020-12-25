Chart.defaults.global.pointHitDetectionRadius = 1;
Chart.defaults.global.tooltips.enabled = false;
Chart.defaults.global.tooltips.mode = 'index';
Chart.defaults.global.tooltips.position = 'nearest';
Chart.defaults.global.tooltips.custom = coreui.ChartJS.customTooltips;
Chart.defaults.global.defaultFontColor = '#646470';
Chart.defaults.global.responsiveAnimationDuration = 1;
document.body.addEventListener('classtoggle', function (event) {
    if (event.detail.className === 'c-dark-theme') {
        if (document.body.classList.contains('c-dark-theme')) {
            cardChart1.data.datasets[0].pointBackgroundColor = coreui.Utils.getStyle('--primary-dark-theme');
            cardChart2.data.datasets[0].pointBackgroundColor = coreui.Utils.getStyle('--info-dark-theme');
            Chart.defaults.global.defaultFontColor = '#fff';
        } else {
            cardChart1.data.datasets[0].pointBackgroundColor = coreui.Utils.getStyle('--primary');
            cardChart2.data.datasets[0].pointBackgroundColor = coreui.Utils.getStyle('--info');
            Chart.defaults.global.defaultFontColor = '#646470';
        }
    }
});
