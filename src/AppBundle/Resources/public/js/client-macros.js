$(document).ready(function() {

  if(!clientMacros || clientMacros.length == 0) {
    return;
  }

  let data = JSON.parse(clientMacros);

  let macroChartDates = data.map(entry => {
    return moment(entry.date).format('MMM Do YY');
  });

  let macroChartKcals = data.map(entry => {
    return entry.kcal;
  });

  let max = Math.max(...macroChartKcals) + 500;
  let min = Math.min(...macroChartKcals) - 1000;

  new Chart(document.getElementById("clientMacrosChart"), {
    type: 'bar',
    data: {
      labels: macroChartDates,
      datasets: [
        {
          label: "Kcals",
          backgroundColor: "rgba(40, 149, 241, 0.5)",
          data: macroChartKcals
        }
      ]
    },
    options: {
      legend: { display: false },
      responsive: true,
      maintainAspectRatio: false,
      title: {
        display: true,
        text: 'Macros tracked by client'
      },
      scales: {
        yAxes: [{
            ticks: {
                max: max,
                min: min
            }
        }]
      }
    }
  });

});
