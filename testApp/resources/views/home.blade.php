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
                    <div class="col-6 col-lg-3 bg-primary-subtle" id="lightDelays">
                        <div class="form-check form-switch custom-checkbox">
                            <input class="form-check-input" type="checkbox" id="gpio3" checked>
                            <label class="form-check-label" for="gpio3">Main light</label>
                        </div>
                        <div class="form-check form-switch custom-checkbox">
                            <input class="form-check-input" type="checkbox" id="gpio4" checked>
                            <label class="form-check-label" for="gpio4">Sleep light</label>
                        </div>
                    </div>
                    <div class="col-6 col-lg-2 bg-primary-subtle justify-content-center d-flex flex-column rounded-end">
                        <div class="form-check form-switch custom-checkbox">
                            <input class="form-check-input" type="checkbox" id="automode"
                                {{ $systemStatus->automode ? 'checked' : '' }}>
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
        <div class="container mt-2 text-white bg-dark p-2 rounded overflow-hidden" data-bs-theme="dark">
            <h2>Sensor Panel</h2>
            <div id="sensor-view">
                <div class="row">
                    <div class="">Human presence:
                        <span id="human">No</span>
                    </div>
                    <div class="mt-0">Light:
                        <span id="light">No</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="container mt-2 text-white bg-dark p-2 rounded overflow-hidden" data-bs-theme="dark">
            <h2>Settings</h2>
            <div>
                <div class="row g-3">
                    <label for="sleepTime" class="col-sm-2 col-form-label">Sleep time</label>
                    <div class="col-auto">
                        <input type="time" class="form-control" id="sleepTime"
                            value="{{ isset($systemStatus) ? \Carbon\Carbon::parse($systemStatus->sleep_mode_time)->format('H:i') : '' }}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary mb-3" id="btnSleepTime">Confirm</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="loadingModalLabel" aria-hidden="true"
            data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-body text-center bg-dark rounded-2 text-white">
                        <div class="spinner-border text-primary my-4" style="width: 3rem; height: 3rem;" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div>
                            Please wait while data is being uploaded.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            $(document).ready(function() {
                function showLoadingModal() {
                    var myModal = new bootstrap.Modal(document.getElementById('loadingModal'), {
                        keyboard: false,
                        backdrop: 'static'
                    });
                    myModal.show();
                }

                function hideLoadingModal() {
                    var myModalEl = document.getElementById('loadingModal');
                    var modal = bootstrap.Modal.getInstance(myModalEl);
                    modal.hide();
                }

                if (!$('#automode').is(':checked')) {
                    $("#lightDelays input").prop("disabled", true);
                } else {
                    $("#lightDelays input").prop("disabled", false);
                }

                setInterval(function() {
                    $.get("{{ route('delays') }}", function(data) {
                        $.each(data, function(key, value) {
                            if (key === 'light') {
                                value = (value === 0) ? 'Yes' : 'No';
                                $('#light').text(value);
                            } else
                            if (key === 'human') {
                                value = (value === 1) ? 'Yes' : 'No';
                                $('#human').text(value);
                            } else {
                                var isChecked = (value === "LOW");
                                if (key === 'automode') {
                                    isChecked = value;
                                    $("#lightDelays input").prop("disabled", isChecked);
                                }
                                $('#' + key).prop('checked', isChecked);
                            }
                        });
                    });
                }, 1500);

                $("#btnSleepTime").click(function() {
                    var sleepTime = $('#sleepTime').val();
                    showLoadingModal();
                    $.get(`/sleeptime/${sleepTime}`)
                        .done(function(data) {
                            //console.log(data.message);
                        })
                        .fail(function(jqXHR, textStatus, errorThrown) {
                            alert("Request failed: " + textStatus + " - " +
                                errorThrown);
                        })
                        .always(function() {
                            setTimeout(() => hideLoadingModal(), 1000);
                        });
                });

                $(".form-check-input").change(function() {
                    if (this.id === 'automode') {
                        var state = $(this).is(':checked') ? 1 : 0;
                        showLoadingModal();
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
                            })
                            .always(function() {
                                setTimeout(() => hideLoadingModal(), 1000);
                            });
                        return;
                    }
                    var pin = this.id.replace('gpio', '');
                    var state = $(this).is(':checked') ? 0 : 1;
                    showLoadingModal();
                    $.get(`/gpio/${pin}/${state}`)
                        .done(function(data) {
                            //console.log(data.message);
                        })
                        .fail(function(jqXHR, textStatus, errorThrown) {
                            alert("Request failed: " + textStatus + " - " +
                                errorThrown);
                        })
                        .always(function() {
                            setTimeout(() => hideLoadingModal(), 1000);
                        });
                });
            });
        </script>
    @endif

@endsection
