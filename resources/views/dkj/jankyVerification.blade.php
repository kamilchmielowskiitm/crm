@extends('layouts.main')
@section('content')
    <link href="{{ asset('/css/dataTables.bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/css/buttons.bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/css/select.bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/css/editor.bootstrap.min.css')}}" rel="stylesheet">
    <style>
        .panel-heading a:after {
            font-family:'Glyphicons Halflings';
            content:"\e114";
            float: right;
            color: grey;
        }
        .panel-heading a.collapsed:after {
            content:"\e080";
        }
        .lenght{
            height: 40px;
        }

        .btn.btn-primary[disabled] {
              background-color: #000;
          }

        .table-striped tr td:first-child + td + td + td + td{
            word-break: break-all;
        }
        .table-striped tr td:first-child + td + td + td + td + td{
            word-break: break-all;
        }

    </style>

    {{--Header page --}}


    <div class="row">
        <div class="col-md-12">
            <div class="page-header">
                <div class="alert gray-nav ">DKJ / Janki Weryfikacja</div>
            </div>
        </div>
     </div>


    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default"  id="panel1">
                <div class="panel-heading">
                    <a data-toggle="collapse" data-target="#collapseOne">
                        Wybierz Oddział
                    </a>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div id="start_stop">
                                <div id="collapseOne" class="panel-collapse collapse in">
                                    <div class="panel-body">
                                        <form action="" method="post" action="dkjRaport">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <label for="exampleInputPassword1" class="showhidetext">Wybierz Oddział</label>
                                            <select class="form-control showhidetext" id="select_form" name="department_id_info" style="border-radius: 0px;">
                                                <optgroup label="-------Wysyłka-------">
                                                @foreach($departments as $department)
                                                    @if($department->type == 'Wysyłka')
                                                        @if(isset($select_department_id_info))
                                                            @if($select_department_id_info == $department->id)
                                                                <option selected value={{$department->id}}>{{$department->department_name.' '.$department->department_type_name}}</option>
                                                            @else
                                                                <option value={{$department->id}}>{{$department->department_name.' '.$department->department_type_name}}</option>
                                                            @endif
                                                        @else
                                                            <option value={{$department->id}}>{{$department->department_name.' '.$department->department_type_name}}</option>
                                                        @endif
                                                    @else
                                                        @if($department->type == 'Badania/Wysyłka')
                                                            @if(isset($select_department_id_info))
                                                                @if($select_department_id_info == $department->id*(-1))
                                                                    <option selected value={{$department->id*(-1)}}>{{$department->department_name.' '.$department->department_type_name.' Wysyłka'}}</option>
                                                                @else
                                                                    <option value={{$department->id*(-1)}}>{{$department->department_name.' '.$department->department_type_name.' Wysyłka'}}</option>
                                                                @endif
                                                            @else
                                                                <option value={{$department->id*(-1)}}>{{$department->department_name.' '.$department->department_type_name.' Wysyłka'}}</option>
                                                            @endif
                                                        @endif
                                                    @endif
                                                @endforeach
                                                <optgroup label="-------Badania-------">                                                @foreach($departments as $department)
                                                    @if($department->type == 'Badania')
                                                        @if(isset($select_department_id_info))
                                                            @if($select_department_id_info == $department->id)
                                                                <option selected value={{$department->id}}>{{$department->department_name.' '.$department->department_type_name}}</option>
                                                            @else
                                                                <option value={{$department->id}}>{{$department->department_name.' '.$department->department_type_name}}</option>
                                                            @endif
                                                        @else
                                                            <option value={{$department->id}}>{{$department->department_name.' '.$department->department_type_name}}</option>
                                                        @endif
                                                    @else
                                                        @if($department->type == 'Badania/Wysyłka')
                                                            @if(isset($select_department_id_info))
                                                                @if($select_department_id_info == $department->id)
                                                                    <option selected value={{$department->id}}>{{$department->department_name.' '.$department->department_type_name.' Badania'}}</option>
                                                                @else
                                                                    <option value={{$department->id}}>{{$department->department_name.' '.$department->department_type_name.' Badania'}}</option>
                                                                @endif
                                                            @else
                                                                <option value={{$department->id}}>{{$department->department_name.' '.$department->department_type_name.' Badania'}}</option>
                                                            @endif
                                                        @endif
                                                    @endif
                                                @endforeach

                                            </select>

                                            <label>Data od:<span style="color:red;">*</span></label>
                                            <div class="input-group date form_date col-md-5" data-date="" data-date-format="yyyy-mm-dd" data-link-field="datak" style="width:100%;">
                                                @if(isset($select_start_date))
                                                    <input class="form-control" name="start_date" type="text" value="{{$select_start_date}}" readonly >
                                                @else
                                                    <input class="form-control" name="start_date" type="text" value="{{date("Y-m-d")}}" readonly >
                                                @endif
                                                <span class="input-group-addon"><span class="glyphicon glyphicon-th"></span></span>
                                            </div>

                                            <label>Data do:<span style="color:red;">*</span></label>
                                            <div class="input-group date form_date col-md-5" data-date="" data-date-format="yyyy-mm-dd" data-link-field="datak" style="width:100%;">
                                                @if(isset($select_stop_date))
                                                    <input class="form-control" name="stop_date" type="text" value="{{$select_stop_date}}" readonly >
                                                @else
                                                    <input class="form-control" name="stop_date" type="text" value="{{date("Y-m-d")}}" readonly >
                                                @endif
                                                <span class="input-group-addon"><span class="glyphicon glyphicon-th"></span></span>
                                            </div>
                                            <br />
                                            <input type="submit" disabled id="search_button" class="form-control showhidetext btn btn-primary" value="Wyświetl" style="
						border-radius: 0px;" name="showjanki">
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if(isset($show_raport))
                <div class="panel panel-default"  id="panel2">
                    <div class="panel-heading">
                        Raport
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div id="start_stop" class="table-responsive">
                                    <table id="datatable" class="thead-inverse table table-striped table-bordered" cellspacing="0" width="100%">
                                        <thead>
                                        <tr>
                                            <th>Data</th>
                                            <th>Dodał</th>
                                            <th>Imie Nazwisko</th>
                                            <th>Telefon</th>
                                            <th>Kampania</th>
                                            <th>Komentarz</th>
                                            <th>Jank</th>
                                            <th>Weryfikacja trenera</th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

    @if(isset($show_raport))
        <!-- Modal -->
        <div id="edit_dkj" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Dodaj Godzin pracownika</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="dtp_input3" class="col-md-5 control-label">Pracownik:</label>
                            <div id="employee_list">
                                <select class="form-control showhidetext" name="users_select" id="users_select" style="border-radius: 0px;">
                                @foreach($users as $user)
                                        <option value={{$user->id}}>{{$user->first_name.' '.$user->last_name}}</option>
                                @endforeach;
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="">Telefon:</label>
                            <input type="text" class="form-control" placeholder="Telefon" name="phone" id="phone" value="">
                        </div>
                        <div class="alert alert-danger" style="display: none" id="alert_phone">
                            Podaj prawidłowy numer telefonu!
                        </div>
                        <div class="form-group">
                            <label for="">Kampania:</label>
                            <input type="text" class="form-control" placeholder="Kampania" name="campaign" id="campaign"  value="">
                        </div>
                        <div class="alert alert-danger" style="display: none" id="alert_campaign">
                            Podaj nazwę kampanii!
                        </div>
                        <div class="form-group">
                            <label for="">Komentarz:</label>
                            <input type="text" class="form-control" placeholder="Komentarz" name="comment" id="comment"  value="">
                        </div>
                        <div class="alert alert-danger" style="display: none" id="alert_comment">
                            Podaj komentarz!
                        </div>
                        <div class="form-group">
                            <label for="">Janek:</label>
                            <select class="form-control showhidetext" name="dkj_status" id="dkj_status" style="border-radius: 0px;">
                                <option value="0">Nie</option>
                                <option value="1">Tak</option>
                            </select>
                        </div>

                        <button id="save_dkj" class="btn btn-primary" name="register" style="font-size:18px; width:100%;">Zapisz</button>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default close" data-dismiss="modal">Anuluj</button>
                    </div>
                </div>
            </div>
        </div>
        @include('dkj.dkjJavaScript');
        @endif

        @endsection
        @section('script')
            <script src="{{ asset('/js/dataTables.bootstrap.min.js')}}"></script>
            <script src="{{ asset('/js/dataTables.buttons.min.js')}}"></script>
            <script src="{{ asset('/js/buttons.bootstrap.min.js')}}"></script>
            <script src="{{ asset('/js/dataTables.select.min.js')}}"></script>
          <script>

            var selected =$("select[id='select_form']").val();

            if (selected != 0) {
                $("#search_button").removeAttr('disabled');
            }

            $("#select_form").on('change', function() {
                var selected =$("select[id='select_form']").val();

                if (selected != 0) {
                    $("#search_button").removeAttr('disabled');
                } else {
                    $("#search_button").attr('disabled', true);
                }
            });

                var action = '';
                var id = -1;
                    $('.form_date').datetimepicker({
                        language: 'pl',
                        autoclose: 1,
                        minView: 2,
                        pickTime: false,
                    });
                    $("#save_dkj").click(function () {



                        var id_user = $("#users_select").val();
                        var phone =$("#phone").val();
                        var dkj_status =$("#dkj_status").val();
                        var comment =$("#comment").val();
                        var campaign =$("#campaign").val();
                        var validation = true;
                        
                        if (phone == '' || isNaN(phone)) {
                            $('#alert_phone').fadeIn(1000);
                            validation = false;
                        } else {
                            $('#alert_phone').fadeOut(1000);
                        }

                        if (comment == '') {
                            $('#alert_comment').fadeIn(1000);
                            validation = false;
                        } else {
                            $('#alert_comment').fadeOut(1000);
                        }

                        if (campaign == '') {
                            $('#alert_campaign').fadeIn(1000);
                            validation = false;
                        } else {
                            $('#alert_campaign').fadeOut(1000);
                        }

                        if (validation == false) {
                            return validation;
                        }

                        $("#save_dkj").attr('disabled', true);
                        $.ajax({
                            type: "POST",
                            url: '{{ route('api.dkjRaportSave') }}',
                            data: {"id_user":id_user,
                                "phone":phone,"dkj_status":dkj_status,
                                "comment":comment,"campaign":campaign,
                                "id":id,"action":action
                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                $("#save_dkj").removeAttr('disabled');
                                $('#edit_dkj').modal('toggle');
                                table.ajax.reload();
                            }
                        });
                    });
                    table = $('#datatable').DataTable({
                        "autoWidth": false,
                        "processing": true,
                        "serverSide": true,
                        "language": {
                            "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Polish.json"
                        },
                        "drawCallback": function (settings) {
                        },
                        dom: '<"lenght"l>Bfrtip',
                        buttons: [{
                                text: 'Edytuj',
                                name: 'edit',        // DO NOT change name
                                id: 'edit',
                                extend: 'selected',
                                action: function ( e, dt, node, config ) {
                                    var data=  dt.rows( { selected: true } ).data();
                                    var id_user = data[0]['id_user'];
                                    id = data[0]['id'];
                                    action = 'edit';
                                    var phone = data[0]['phone'];
                                    var campaign = data[0]['campaign'];
                                    var comment = data[0]['comment'];
                                    var dkj_status = data[0]['dkj_status'];
                                    $('#edit_dkj').modal('show');
                                    $("#users_select").val(id_user);
                                    $("#phone").val(phone);
                                    $("#dkj_status").val(dkj_status);
                                    $("#comment").val(comment);
                                    $("#campaign").val(campaign);
                                }
                            },
                            {
                                extend: 'selected', // Bind to Selected row
                                text: 'Usuń',
                                name: 'delete',      // DO NOT change name
                                id:'delete',
                                action: function ( e, dt, node, config ) {
                                    var data=  dt.rows( { selected: true } ).data();
                                    id = data[0]['id'];
                                    action = 'remove';
                                    $.ajax({
                                        type: "POST",
                                        url: '{{ route('api.dkjRaportSave') }}',
                                        data: {"id":id,"action":action
                                        },
                                        headers: {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                        },
                                        success: function(response) {
                                            table.ajax.reload();
                                        }
                                    });
                                }
                            }],
                        "ajax": {
                            'url': "{{ route('api.datatableDkjRaport') }}",
                            'type': 'POST',
                            'data': function (d) {
                                d.start_date = $("input[name='start_date']").val();
                                d.stop_date = $("input[name='stop_date']").val();
                                d.department_id_info = $("select[name='department_id_info']").val()
                                d.type_verification = 1;
                            },
                            'headers': {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
                        }, "columns": [
                            {"data": "add_date"},
                            {
                                "data": function (data, type, dataToSet) {
                                    return data.dkj_user_first_name + " " + data.dkj_user_last_name;
                                }, "name": " dkj_user.last_name"
                            },
                            {
                                "data": function (data, type, dataToSet) {
                                    return data.user_first_name + " " + data.user_last_name;
                                }, "name": "user.last_name"
                            },
                            {"data": "phone"},
                            {"data": "campaign"},
                            {"data": "comment"},
                            {"data": function (data, type, dataToSet) {
                                if(data.dkj_status == 0)
                                    return 'Nie';
                                else return 'Tak'
                            }, "name": "dkj_status"},
                            {
                                "data": function (data, type, dataToSet) {
                                    if(data.manager_status == 1)
                                    {
                                        return "Nie " + " " + data.comment_manager;
                                    }else if(data.manager_status == null)
                                    {
                                        return "Brak weryfikacji";
                                    }else
                                        return "Tak "+ " " + data.comment_manager;
                                }, "name": " dkj.comment_manager"
                            }
                        ],
                        select: true
                    });

            </script>
@endsection
