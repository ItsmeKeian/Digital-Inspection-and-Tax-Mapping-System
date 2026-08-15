$(document).ready(function(){
    loadDashboard();
});

function loadDashboard(){
    $.get("php/get/get_dashboard.php", function(data){
        let d = JSON.parse(data);

        // Stat cards — count up animation
        animateCount("totalBusinesses", d.total);
        animateCount("inspectedCount", d.inspected);
        animateCount("pendingCount", d.pending);
        animateCount("violationsCount", d.violations);

        renderLine(d);
        renderDoughnut(d);
        renderBar(d);
    });
}

function animateCount(id, target){
    let el = document.getElementById(id);
    let start = 0;
    let duration = 800;
    let step = target / (duration / 16);
    let timer = setInterval(function(){
        start += step;
        if(start >= target){
            el.textContent = target;
            clearInterval(timer);
        } else {
            el.textContent = Math.floor(start);
        }
    }, 16);
}

function renderLine(d){
    new Chart(document.getElementById("lineChart"), {
        type: 'line',
        data: {
            labels: d.months,
            datasets: [{
                label: "Inspections",
                data: d.monthly,
                borderColor: "#C8960C",
                backgroundColor: function(context){
                    let chart = context.chart;
                    let {ctx, chartArea} = chart;
                    if(!chartArea) return "transparent";
                    let gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                    gradient.addColorStop(0, "rgba(200,150,12,0.18)");
                    gradient.addColorStop(1, "rgba(200,150,12,0)");
                    return gradient;
                },
                fill: true,
                tension: 0.4,
                borderWidth: 2.5,
                pointBackgroundColor: "#C8960C",
                pointBorderColor: "#fff",
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: "#1C1400",
                    titleColor: "#C8960C",
                    bodyColor: "#fff",
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(ctx){ return " " + ctx.parsed.y + " inspection(s)"; }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: "#F3F4F6", drawBorder: false },
                    ticks: { color: "#9CA3AF", font: { size: 11 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: "#9CA3AF", font: { size: 11 } }
                }
            }
        }
    });
}

function renderDoughnut(d){
    new Chart(document.getElementById("statusChart"), {
        type: 'doughnut',
        data: {
            labels: ['Inspected', 'Pending', 'Violations'],
            datasets: [{
                data: [d.inspected, d.pending, d.violations],
                backgroundColor: ["#C8960C", "#F5C518", "#EF4444"],
                borderWidth: 0,
                hoverOffset: 6
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: { size: 12 },
                        padding: 16,
                        usePointStyle: true,
                        pointStyleWidth: 8,
                        color: "#6B7280"
                    }
                },
                tooltip: {
                    backgroundColor: "#1C1400",
                    titleColor: "#C8960C",
                    bodyColor: "#fff",
                    padding: 10,
                    cornerRadius: 8
                }
            },
            cutout: '68%'
        }
    });
}

function renderBar(d){
    new Chart(document.getElementById("barChart"), {
        type: 'bar',
        data: {
            labels: d.barangays,
            datasets: [{
                label: "Businesses",
                data: d.counts,
                backgroundColor: function(context){
                    let chart = context.chart;
                    let {ctx, chartArea} = chart;
                    if(!chartArea) return "#C8960C";
                    let gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                    gradient.addColorStop(0, "#F5C518");
                    gradient.addColorStop(1, "#C8960C");
                    return gradient;
                },
                borderRadius: 8,
                borderSkipped: false,
                borderWidth: 0
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: "#1C1400",
                    titleColor: "#C8960C",
                    bodyColor: "#fff",
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(ctx){ return " " + ctx.parsed.y + " business(es)"; }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: "#F3F4F6", drawBorder: false },
                    ticks: { color: "#9CA3AF", font: { size: 11 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: "#9CA3AF", font: { size: 11 }, maxRotation: 45 }
                }
            }
        }
    });
}