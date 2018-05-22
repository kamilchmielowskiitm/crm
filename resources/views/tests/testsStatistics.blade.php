@extends('layouts.main')
@section('content')
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<div class="row">
    <div class="col-md-12">
        <div class="page-header">
            <div class="alert gray-nav">Testy / Statystyki testów</div>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-md-6">
        <div id="donutchart" style="width: 100%; height: 550px;"></div>
    </div>
    <div class="col-md-6">
        <div id="piechart" style="width: 100%; height: 550px;"></div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-info">
            <div class="panel-heading">
                Sprawdź statystyki pracownika
            </div>
            <div class="panel-body">
                @if($redirect == 1)
                    <form method="POST" action="{{ URL::to('/tests_statistics') }}" id="user_form">
                @else
                    <form method="POST" action="{{ URL::to('/tests_statistics_coach') }}" id="user_form">
                @endif
                        <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                    <div class="form-group">
                        <select class="form-control" name="user_id" id="user_id">
                            <option value="0">Wybierz</option>
                            @foreach($users as $user)
                                <option value="{{$user->id}}">{{$user->last_name . ' ' . $user->first_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-info" id="show_user">
                            Pokaż statystyki
                        </button>   
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>

$(document).ready(function() {
    $('#show_user').click(function(e) {
        e.preventDefault();
        
        var user_id = $('#user_id').val();

        if (user_id == 0) {
            swal('Wybierz użytkownika!')
            return
        }

        $('#user_form').submit();
    });
});

google.charts.load("current", {packages:["corechart"]});
    google.charts.setOnLoadCallback(drawChart);
    function drawChart() {
        var data = google.visualization.arrayToDataTable([
        ['Task', 'Hours per Day'],
        ['Zaliczone', {{$results->good}}],
        ['Niezaliczone', {{$results->bad}}]
        ]);

        var options = {
        title: 'Ogólna ocena testów',
        pieHole: 0.4,
        colors: ['#53e041', '#d81c32', '#dae23d']
        };

        var chart = new google.visualization.PieChart(document.getElementById('donutchart'));
        chart.draw(data, options);
    }

google.charts.load("current", {packages:["corechart"]});
        google.charts.setOnLoadCallback(drawChartDep);
        function drawChartDep() {
            var data = google.visualization.arrayToDataTable([
            ['Language', 'Speakers (in millions)'],
            @foreach($departments_stats as $item)
                ['{{$item->dep_name . ' ' .  $item->dep_type_name}}', {{$item->dep_sum}}],
            @endforeach
            ['', 0]
        ]);

        var options = {
            legend: 'none',
            pieSliceText: 'label',
            title: 'Ilość testów w oddziałach',
            pieStartAngle: 100,
      };

        var chart = new google.visualization.PieChart(document.getElementById('piechart'));
        chart.draw(data, options);
      }

</script>
@endsection
