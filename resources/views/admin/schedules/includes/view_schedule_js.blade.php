

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" integrity="sha512-BNaRQnYJYiPSqHHDb58B0yaPfCu+Wgds8Gp/gU33kqBtgNS4tSPHuGibyoeqMV/TJlSKda6FXzoEyYGjTe+vXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    var par = document.getElementById("spectograph").getContext("2d");
    var easypar = document.getElementById("easy-spectograph").getContext("2d");
    var myChart;
    $(document).on('click', '.view-schedule', function () {
        $('#loader').show();
        let id = $(this).data('id');
        let url = "{{ route('admin.schedule.getScheduleData',['id' => ':id']) }}".replace(':id', id);
        $.ajax({
            url: url,
            method: 'GET',
            success: function (res) {
                var moonPath = "{{url('assets/img/')}}";
                $('.graph-moon').attr('src', moonPath + '/moon-'+res.hours.moon+'.png');
                $('#timeLast').html(res.hours.time0);
                $('#time0').html(res.hours.time0);
                $('#time1').html(res.hours.time1).css({"left": res.hours.time1_gap + 'px'});
                $('#time2').html(res.hours.time2).css({"left": res.hours.time2_gap + 'px'});
                $('#time3').html(res.hours.time3).css({"left": res.hours.time3_gap + 'px'});
                $('#time4').html(res.hours.time4).css({"left": res.hours.time4_gap + 'px'});
                $('#time5').html(res.hours.time5).css({"left": res.hours.time5_gap + 'px'});
                var data = {
                    datasets: res.data
                };

                var option = {
                    legend: {
                        display: false
                    },
                    plugins: {
                        tooltip: {
                            enabled: false // <-- this option disables tooltips
                        }
                    },
                    scales: {
                        yAxes: [{
                            stacked: true,
                            gridLines: {
                                display: true,
                                color: "rgba(255,99,132,0.2)"
                            },
                            ticks: {
                                max: 100,
                            },
                        }],
                        xAxes: [{
                            display: false,
                            stacked: true,
                            scaleLabel: {
                                display: true,
                                labelString: 'frequency'
                            },
                            type: 'linear',
                            ticks: {
                                min: res.hours.min,
                                max: res.hours.max
                            },
                            gridLines: {
                                display: true,
                                color: "rgba(255,99,132,0.2)"
                            }
                        }]
                    },
                    elements: {
                        line: {
                            tension: 0, // bezier curves
                        }
                    }
                };

                Chart.Scatter(par, {
                    options: option,
                    data: data
                });

                $('#graph-modal').modal();
                $('#loader').hide();
            },
            error: function () {
                $('#loader').hide();
                alert('Error while generating graph');
            }
        });

    });
    $(document).on('click', '.easy-view-schedule', function () {
        $('#loader').show();
        let id = $(this).data('id');
        let url = "{{ route('admin.schedule.getEasyScheduleData',['id' => ':id']) }}".replace(':id', id);
        $.ajax({
            url: url,
            method: 'GET',
            success: function (res) {
                $('#easy-timeLast').html(res.hours.time3);
                $('#easy-time0').html(res.hours.time0);
                $('#easy-time1').html(res.hours.time1).css({"left": res.hours.time1_gap + 'px'});
                $('#easy-time2').html(res.hours.time2).css({"left": res.hours.time2_gap + 'px'});
                var data = {
                    datasets: res.data
                };

                var option = {
                    legend: {
                        display: false
                    },
                    plugins: {
                        tooltip: {
                            enabled: false // <-- this option disables tooltips
                        }
                    },
                    scales: {
                        yAxes: [{
                            stacked: true,
                            gridLines: {
                                display: true,
                                color: "rgba(255,99,132,0.2)"
                            },
                            ticks: {
                                max: 100,
                            },
                        }],
                        xAxes: [{
                            display: false,
                            stacked: true,
                            scaleLabel: {
                                display: true,
                                labelString: 'frequency'
                            },
                            type: 'linear',
                            ticks: {
                                min: res.hours.min,
                                max: res.hours.max
                            },
                            gridLines: {
                                display: true,
                                color: "rgba(255,99,132,0.2)"
                            }
                        }]
                    },
                    elements: {
                        line: {
                            tension: 0, // bezier curves
                        }
                    }
                };

                Chart.Scatter(easypar, {
                    options: option,
                    data: data
                });

                $('#easy-graph-modal').modal();
                $('#loader').hide();
            },
            error: function () {
                $('#loader').hide();
                alert('Error while generating graph');
            }
        });

    });

</script>

{{-- save image --}}
<script>

    function downloadURI(uri, name) {
        var link = document.createElement("a");

        link.download = name;
        link.href = uri;
        alert(uri);
        document.body.appendChild(link);
        link.click();
    }

    function DownloadAsImage(schID, id) {
        var element = $("#" + schID)[0];
        html2canvas(element).then(function (canvas) {
            var myImage = canvas.toDataURL('image/jpeg', 0.9);
            $.post("{{ route('admin.schedules.ajax.store.graph.image') }}",{ id: id, image: myImage, _token: '{{ csrf_token() }}' }, function(data){
                console.log(data);
            });
            // downloadURI(myImage, "graph-image.png");
        });
    }

    @if(request('id'))
        var sch = '{{ request("id") }}';
        $('#schedule-'+sch).click();
        mode = $('#schedule-'+sch).attr('data-mode');
        if(mode  == '2')
        {
            schId = 'spectograph';
        }else{
            schId = 'easy-spectograph';
        }
        setTimeout(() => {
            DownloadAsImage(schId, sch);
        }, 3000);

    @endif

</script>
