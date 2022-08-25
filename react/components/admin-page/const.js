import NumberFormat from "./components/common/numberFormat";

export const PRIMARYCOLOR = "#0062FF"

/*BarChart*/
export const OPTIONS ={
    responsive: false,
    legend: {
        display: false
    },
    scales: {
        xAxes: [{
            gridLines: {
                display:false
            },
            ticks: {
                display: false,
                beginAtZero: false,
            }
        }],
        yAxes: [{
            gridLines: {
                display:false
            },

        }]
    },
    "hover": {
        "animationDuration": 0
    },
    "animation": {
        "duration": 1,
        "onComplete": function() {
            var chartInstance = this.chart,
                ctx = chartInstance.ctx;

            ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontSize, Chart.defaults.global.defaultFontStyle, Chart.defaults.global.defaultFontFamily);
            ctx.textAlign = 'center';
            ctx.textBaseline = 'bottom';
            let defer = ''

            this.data.datasets.forEach(function(dataset, i) {
                var meta = chartInstance.controller.getDatasetMeta(i);
                meta.data.forEach(function(bar, index) {
                    var data = dataset.data[index];
                    if(bar._model.datasetLabel === "Sales revenue" ){
                        defer = 35
                    } else {
                        defer = 25
                    }
                    ctx.fillText(NumberFormat(data), bar._model.x+defer, bar._model.y+7);
                });
            });
        }
    },
    title: {
        display: false,
        text: ''
    },
    layout: {
        padding: {
            left: -6,
            right:35
        }
    },
    tooltips: {
        enabled: false
    }
}
