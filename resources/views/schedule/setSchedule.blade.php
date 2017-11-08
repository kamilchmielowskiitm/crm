@extends('layouts.main')
@section('content')
<style>
    button{
        width: 100%;
        height: 50px;
    }
    td.details-control {
        background: url({{ asset('/image/details_open.png')}}) no-repeat center center;
        cursor: pointer;
    }
    tr.shown td.details-control {
        background: url({{ asset('/image/details_close.png')}}) no-repeat center center;
    }
    td{
        text-align: center;
    }
    .reason{
        width: 101px;
    }
</style>

{{--Header page --}}
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">Ustal Grafik</h1>
        </div>
    </div>


    <div class="row">
        <div class="col-lg-12">

            <div class="panel panel-default">
                <div class="panel-heading">
                    Ustal Grafik
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div id="start_stop">
                                <div class="panel-body">
                                    <div class="col-md-6">
                                        <div class="well">
                                            <h1 style ="font-family: 'bebas_neueregular',sans-serif; margin-top:0px;text-shadow: 2px 2px 2px rgba(150, 150, 150, 0.8); font-size:25px;">Wybierz tydzień:</h1>
                                            <form class="form-horizontal" method="post" action="set_schedule">
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                <select class="form-control" name="show_schedule" id="week_text">
                                                    <option>Wybierz</option>
                                                    @for ($i=0; $i < 5; $i++)
                                                        @php
                                                        $przelicznik = 7*$i;
                                                        $data = date("W",mktime(0,0,0,date("m"),date("d")+$przelicznik,date("Y"))); // numer tygodnia.
                                                        $data_czytelna =  date("Y.m.d", mktime(0,0,0,1,1+($data*7)-6,date("Y"))); // poniedziałek
                                                        $data_czytelna2 =  date("Y.m.d", mktime(0,0,0,1,(1+($data*7)-4)+4,date("Y"))); // niedziela
                                                        @endphp
                                                        @if (isset($number_of_week))
                                                            @if ($data == $number_of_week)
                                                                <option value={{$data}} selected>{{$data_czytelna.' -> '.$data_czytelna2}}</option>;
                                                            @else
                                                                <option value={{$data}}>{{$data_czytelna.' -> '.$data_czytelna2}}</option>;
                                                            @endif
                                                        @else
                                                            @if ($data == date("W"))
                                                                <option value={{$data}} selected>{{$data_czytelna.' -> '.$data_czytelna2}}</option>;
                                                            @else
                                                                <option value={{$data}}>{{$data_czytelna.' -> '.$data_czytelna2}}</option>;
                                                            @endif
                                                        @endif
                                                    @endfor
                                                </select></br>
                                                <button type="submit" class="btn btn-primary" name="show_week_grafik_send" style="font-size:18px; width:100%;">Wyszukaj</button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="well">
                                            <h1 style ="font-family: 'bebas_neueregular',sans-serif; margin-top:0px;text-shadow: 2px 2px 2px rgba(150, 150, 150, 0.8); font-size:25px;">Kolory:</h1>
                                            <table class="table table-bordered">
                                                <tr>
                                                    <td align="center" style="width: 40px;background-color:#ff7070;"><b></b></td>
                                                    <td align="center"><b>Zbyt mało osób</b></td>
                                                </tr>
                                                <tr>
                                                    <td align="center" style="width: 40px;background-color:#ffee29;"><b></b></td>
                                                    <td align="center"><b>Za dużo osób</b></td>
                                                </tr>
                                            </table>
                                        </div>
                                </div>
                                    <div class="col-md-12">
                                        @if (isset($number_of_week))
                                            <table class="table table-bordered">
                                                <div class="panel-heading" style="border:1px solid #d3d3d3;"><h4><b>Analiza Grafik Plan</b></h4></div>
                                                <tr>
                                                    <td align="center"><b>Godzina</b></td>
                                                    <?php $week_array = ['Pon','Wt','Śr','Czw','Pt','Sob','Nie']; ?>
                                                    @for($i=8;$i<21;$i++)
                                                    <td align="center"><b>{{$i}}</b></td>
                                                    @endfor
                                                </tr>
                                                @foreach($schedule_analitics as $item =>$key)
                                                    <?php $lp = 8;
                                                        $number_day_of_week = 0;?>
                                                    @foreach($key as $item2 =>$key2)
                                                        @if($lp == 8)
                                                            <tr>
                                                                <td>{{$week_array[$number_day_of_week++]}}</td>
                                                        @endif
                                                        @if($lp<= 21)
                                                         <td align="center"><b>{{$key2}}</b></td>
                                                                    <?php $lp++; ?>
                                                        @endif
                                                        @if($lp >20)
                                                            <?php $lp = 8; ?>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                @endforeach
                                            </table>

                                            <table id="datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                                <thead>
                                                <tr>
                                                    <th></th>
                                                    <th>Imię</th>
                                                    <th>Nazwisko</th>
                                                    <th>Telefon</th>
                                                    <th>Grafik</th>
                                                </tr>
                                                </thead>
                                                <tbody>

                                                </tbody>
                                            </table>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.js"></script>
<script>
    moment().format();
    function format ( d ) {
        var start_work = Array(d.monday_start,d.tuesday_start,d.wednesday_start,d.thursday_start,d.friday_start,d.saturday_start,d.sunday_start);
        var stop_work = Array(d.monday_stop,d.tuesday_stop,d.wednesday_stop,d.thursday_stop,d.friday_stop,d.saturday_stop,d.sunday_stop);
        var reason = Array(d.monday_comment,d.tuesday_comment,d.wednesday_comment,d.thursday_comment,d.friday_comment,d.saturday_comment,d.sunday_comment);
        var week_array = ['Pon','Wt','Śr','Czw','Pt','Sob','Nie'];
        var day = $("#week_text option:selected").text();
        day = day.split(" ");
        var start_date = moment(day[0], "YYYY.MM.DD");
        var table = '<table class="table-bordered">'+
            '<thead>' +
            '<tr>';
            for(var i=0;i<7;i++)
            {
                if(i==0)
                    table+='<th>'+week_array[i]+'. '+start_date.add(0, 'days').format('DD-MM')+'</th>';
                else
                    table+='<th>'+week_array[i]+'. '+start_date.add(1, 'days').format('DD-MM')+'</th>';
            }
            table += '<th>Akcja</th></tr>'+
            '</thead>' +
            '<tbody> <tr id='+d.id_user+'>';
            var time = moment('07'+':'+'45','HH:mm');
            for(var i=0;i<7;i++)
            {
                table +='<td class='+d.id+'>';
                if(reason[i] != null)
                    table+='<div class="hour" style="display: none;">';
                else
                    table+='<div class="hour">';

                table+= '<select name='+week_array[i]+'_start_work class="form-control">'+
                '<option value='+null+'>Wybierz</option>';
                while(time.format("HH")!='21')
                {
                    time.add(15,'m');
                    if(start_work[i] != null && start_work[i] == time.format("HH:mm:ss"))
                    {
                        table+='<option selected>'+time.format("HH:mm")+'</option>';
                    }else
                    table+='<option>'+time.format("HH:mm")+'</option>';
                }
                table+='</select>';
                table+='<span class="glyphicon glyphicon-arrow-down"></span>';

                time = moment('08'+':'+'00','HH:mm');
                table+='<select name='+week_array[i]+'_stop_work class="form-control">'+
                    '<option>Wybierz</option>';
                while(time.format("HH")!='21')
                {
                    time.add(15,'m');
                    if(stop_work[i] != null && stop_work[i] == time.format("HH:mm:ss")) {
                        table += '<option selected>' + time.format("HH:mm") + '</option>';
                    }else
                    {
                        table += '<option>' + time.format("HH:mm") + '</option>';
                    }
                }
                table+='</select></div>';
                if(reason[i] != null) {
                    table += '<div class="reason">';
                    table += '<input type="text" value="' + reason[i] + '" name=' + week_array[i] + '_reason class="form-control" placeholder="Powód">';
                }
                    else{
                        table+='<div class="reason" style="display: none;">';
                        table+= '<input type="text" name='+week_array[i]+'_reason class="form-control" placeholder="Powód">';
                }
                table+=
                    '</div><p>';
                if(reason[i] != null)
                    table+='<input type="checkbox" checked class="checkbox '+week_array[i]+'_reasonCheck">Wolne';
                else
                    table+='<input type="checkbox" class="checkbox '+week_array[i]+'_reasonCheck">Wolne';

                    '</p></td>';
                time = moment('08'+':'+'00','HH:mm');
            }
        table+=
            '<td>'+
            '<button type="submit" id='+d.id+' class="btn btn-primary saved" name="save_schedule">Zapisz</button>'+
            '</td>'+
        '</tr>';

            table +='</tbody>';
        return table+'</table>';

    }
    $(document).ready(function() {
        var year = $("#week_text option:selected").text();
        var week_number = $("select[name='show_schedule']").val();
        year = year.split(".");
        var start_date = moment(year).add(week_number, 'weeks').startOf('week').format('DD MM YYYY');
        var stop_date =  moment(year).add(week_number, 'weeks').startOf('isoweek').format('DD MM YYYY');

        table = $('#datatable').DataTable({
            "autoWidth": false,
            "processing": true,
            "serverSide": true,
            "drawCallback": function (settings) {
            },
            "ajax": {
                'url': "{{ route('api.datatableShowUserSchedule') }}",
                'type': 'POST',
                'data': function (d) {
                    d.year = year[0];
                },
                'headers': {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
            }, "columns": [
                {
                    "className": 'details-control',
                    "orderable": false,
                    "data": null,
                    "defaultContent": '',
                    "searchable": false
                },
                {"data": "user_first_name", "name": "first_name"},
                {"data": "user_last_name", "name": "last_name"},
                {"data": "user_phone", "name": "phone"},
                {
                    "data": function (data, type, dataToSet) {
                        if (data.id == null)
                            return 'Nie';
                        else return 'Tak'
                    }, "name": "id"
                },
            ],
            select: true
        });


        $('#datatable tbody').on('click', 'td.details-control', function () {
            var tr = $(this).closest('tr');
            var row = table.row( tr );

            if ( row.child.isShown() ) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
            }
            else {
                // Open this row
                row.child( format(row.data()) ).show();
                tr.addClass('shown');
            }
            $('.checkbox').change(function(){
                if( $(this).is(':checked') )
                {
                     $(this).closest('td').find('.hour').hide();
                     $(this).closest('td').find('.reason').show();
                }else{
                    $(this).closest('td').find('.hour').show();
                    $(this).closest('td').find('.reason').hide();
                }
            });

            $(".saved").click(function(){
                var week_array = ['Pon','Wt','Śr','Czw','Pt','Sob','Nie'];
                var $start_hour_array = new Array();
                var $stop_hour_array = new Array();
                var $reason_array  = new Array();
                var closestTR =  $(this).closest('tr');
                var id_user = closestTR.attr('id');
                var schedule_id =  $(this).attr('id');
                var checkbox;
                var valid = true;
                var time = true;
                for(var i=0;i<week_array.length;i++)
                {
                    checkbox = closestTR.find('.'+week_array[i]+"_reasonCheck");
                    $start_hour_array.push(closestTR.find("select[name="+week_array[i]+"_start_work]").val());
                    $stop_hour_array.push(closestTR.find("select[name="+week_array[i]+"_stop_work]").val());
                    $reason_array.push(closestTR.find("input[name="+week_array[i]+"_reason]").val());
                    if(($start_hour_array[i] == "null" || $stop_hour_array[i] == "null") && !checkbox.is(':checked'))
                    {
                        valid = false;
                    }
                    else if($start_hour_array[i] > $stop_hour_array[i] && !checkbox.is(':checked'))
                    {
                        valid = false;
                        time = false;
                    }
                    if(checkbox.is(':checked'))
                    {
                        $start_hour_array[i] = null;
                        $stop_hour_array[i] = null;
                    }
                }
                console.log(valid);
                if(valid == true)
                {
                    $.ajax({
                        type: "POST",
                        url: '{{ route('api.saveSchedule') }}',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data:{"start_hours":$start_hour_array,"stop_hours":$stop_hour_array,"reasons":$reason_array,"id_user":id_user,"schedule_id":schedule_id},
                        success: function(response) {
                            location.reload();
                        }
                    });
                }else {
                    if(time == false)
                    {
                        alert("Godziny są nieprawidłowe");
                    }else
                    alert("Nie wszystkie dane zostały uzupełnion.");
                }

            });
        } );
    });

</script>
@endsection