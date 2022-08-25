function chartDrawSegmentValues(pieChart) {
  var ctx = pieChart.chart.ctx;

  ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontFamily, 'normal', Chart.defaults.global.defaultFontFamily);
  ctx.textAlign = 'center';
  ctx.textBaseline = 'bottom';

  pieChart.data.datasets.forEach(function (dataset) {
    for (var i = 0; i < dataset.data.length; i++) {
      var model = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._model;
      var total = dataset._meta[Object.keys(dataset._meta)[0]].total;
      var midRadius = model.innerRadius + (model.outerRadius - model.innerRadius)/2;
      var startAngle = model.startAngle;
      var endAngle = model.endAngle;
      var midAngle = startAngle + (endAngle - startAngle)/2;

      var x = midRadius * Math.cos(midAngle);
      var y = midRadius * Math.sin(midAngle);

      ctx.fillStyle = '#fff';

      if (i == 3){ // Darker text color for lighter background
        ctx.fillStyle = '#444';
      }

      var percent = String(Math.round(dataset.data[i]/total*100)) + '%';

      // ctx.fillText(dataset.data[i], model.x + x, model.y + y);
      // Display percent in another line, line break doesn't work for fillText
      ctx.fillText(percent, model.x + x, model.y + y);
    }
  });
}
