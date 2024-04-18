@extends('layouts.body')

@section('title', 'Home Page')

@section('main')

    <div class="container text-white mt-5">
        <h1>Welcome to our website</h1>
        @if (Auth::check())
            <div class="mb-3">
                <p class="text-bg-dark p-2 rounded-1 d-inline">Status: <span class="text-success">Logged in</span></p>
            </div>
            <a href="{{ route('logout') }}" class="btn btn-primary">Logout</a>
        @else
            <div class="mb-3">
                <p class="text-bg-dark p-2 rounded-1 d-inline">Status: <span class="text-danger">Not logged in</span></p>
            </div>
            <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
            {{-- <a href="{{ route('register') }}" class="btn btn-secondary">Register</a> --}}
        @endif
    </div>
    @if (Auth::check())
        <style>
            .custom-checkbox .form-check-input {
                transform: scale(1.5);
            }
            .custom-checkbox .form-check-label {
                font-size: 1.3rem;
                margin-left: 1.5rem;
            }
            .custom-checkbox {
                height: 3rem;
                margin-left: 3rem;
                align-items: center;
                display: flex;
            }
        </style>
        <div class="container mt-5 text-white bg-dark p-2 rounded overflow-hidden" data-bs-theme="dark">
            <h2>Delays Control Panel</h2>
            <div id="gpio-controls">
                <div class="row">
                    <div class="col-2 bg-primary-subtle" id="lightDelays">
                        <div class="form-check form-switch custom-checkbox">
                            <input class="form-check-input" type="checkbox" id="gpio3" checked>
                            <label class="form-check-label" for="gpio3">Delay 1</label>
                        </div>
                        <div class="form-check form-switch custom-checkbox">
                            <input class="form-check-input" type="checkbox" id="gpio4" checked>
                            <label class="form-check-label" for="gpio4">Delay 2</label>
                        </div>
                    </div>
                    <div class="col-2 bg-primary-subtle justify-content-center d-flex flex-column rounded-end">
                        <div class="form-check form-switch custom-checkbox">
                            <input class="form-check-input" type="checkbox" id="automode" checked>
                            <label class="form-check-label" for="automode">Auto</label>
                        </div>
                    </div>
                </div>
                <div class="form-check form-switch custom-checkbox">
                    <input class="form-check-input" type="checkbox" id="gpio6" checked>
                    <label class="form-check-label" for="gpio6">Delay 3</label>
                </div>
                <div class="form-check form-switch custom-checkbox">
                    <input class="form-check-input" type="checkbox" id="gpio9" checked>
                    <label class="form-check-label" for="gpio9">Delay 4</label>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function() {
                $.get("{{ route('delays') }}", function(data) {
                    $.each(data, function(key, value) {
                        var isChecked = (value === "LOW");
                        if (key === 'automode') {
                            isChecked = value;
                            $("#lightDelays input").prop("disabled", isChecked);
                        }
                        $('#' + key).prop('checked', isChecked);
                    });
                });

                $(".form-check-input").change(function() {
                    if (this.id === 'automode'){
                        var state = $(this).is(':checked') ? 1 : 0;
                        $.get(`/auto/${state}`)
                            .done(function(data) {
                                //console.log(data.message);
                                if (state === 1) {
                                    $("#lightDelays input").prop("disabled", true);
                                    return;
                                } else {
                                    $("#lightDelays input").prop("disabled", false);
                                }
                            })
                            .fail(function(jqXHR, textStatus, errorThrown) {
                                alert("Request failed: " + textStatus + " - " +
                                    errorThrown);
                            });
                        return;
                    }
                    var pin = this.id.replace('gpio', '');
                    var state = $(this).is(':checked') ? 0 : 1;
                    $.get(`/gpio/${pin}/${state}`)
                        .done(function(data) {
                            //console.log(data.message);
                        })
                        .fail(function(jqXHR, textStatus, errorThrown) {
                            alert("Request failed: " + textStatus + " - " +
                                errorThrown);
                        });
                });
            });
        </script>
    @endif

@endsection
