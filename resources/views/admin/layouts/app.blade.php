<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ env('APP_NAME') }} | Dashboard</title>
    <!-- Favicon -->
    <link href="{{ asset('argon') }}/img/brand/favicon.png" rel="icon" type="image/png">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
    <!-- Extra details for Live View on GitHub Pages -->

    <!-- Icons -->
    <link href="{{ asset('argon') }}/vendor/nucleo/css/nucleo.css" rel="stylesheet">
    <link href="{{ asset('argon') }}/vendor/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
    <!-- Argon CSS -->
    <link type="text/css" href="{{ asset('argon') }}/css/argon.css?v=1.0.0" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" integrity="sha512-vKMx8UnXk60zUwyUnUPM3HbQo8QfmNx7+ltw8Pm5zLusl1XIfwcxo8DbWCqMGKaWeNxWA8yrx5v3SaVpMvR3CA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
    @yield('css')
    <link type="text/css" href="{{ asset('assets') }}/css/custom.css" rel="stylesheet">
    <style>
        .acitve_color {
            color: #f4645f !important;
        }

        /*Toastr CSS*/
        .toast-info {
            background-color: green;
        }

        #toast-container>.toast-success {
            opacity: 1 !important;
        }

        #toast-container>.toast-error {
            opacity: 1 !important;
        }

    </style>
</head>

<body class="{{ $class ?? '' }}">
    @auth()
        <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
        @include('admin.layouts.navbars.sidebar')
    @endauth

    <div class="main-content">
        @include('admin.layouts.navbars.navbar')
        @yield('content')
        @include('admin.schedules.includes.view_schedule_modal')

        {{-- Delete Popup Modal --}}
        <div class="modal" id="deleteModalPopup">
            <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title title_text"></h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="body_text"></p>
                </div>

                <!-- Modal footer -->
                <div class="modal-footer">
                    <a href="javascript:void(0);" class="btn btn-primary delete_modal_yes_link">Yes</a>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">No</button>
                </div>
            </div>
            </div>
        </div>
    </div>

    @guest()
        @include('admin.layouts.footers.guest')
    @endguest

    <script src="{{ asset('argon') }}/vendor/jquery/dist/jquery.min.js"></script>
    <script src="{{ asset('argon') }}/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js" integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
    @stack('js')

        <!-- Argon JS -->
        <script src="{{ asset('argon') }}/js/argon.js?v=1.0.0"></script>
        <script src="{{ asset('assets') }}/js/custom.js"></script>
        <script>
            @if(session('success'))
                toastr.success("{{ session('success') }}");
            @elseif(session('error'))
                toastr.error("{{ session('error') }}");
            @endif
            @if(isset($errors->all()[0]))
                toastr.error("{{ $errors->all()[0] }}");
            @endif
            $(document).on('click', '.table-row', function () {
                window.location = $(this).data('href');
            });

            function mapDataToFields(data)
            {
                $.map(data, function(value, index){
                    var input = $('[name="'+index+'"]');
                    if($(input).length && $(input).attr('type') !== 'file')
                    {
                    if(($(input).attr('type') == 'radio' || $(input).attr('type') == 'checkbox') && value == $(input).val())
                        $(input).prop('checked', true);
                    else
                        $(input).val(value).change();
                    }
                });
            }
            var data = <?php echo json_encode(session()->getOldInput()) ?>;
            mapDataToFields(data);

            $(document).on('change', '.filter_input', function(){
                $(".filter_form").submit();
            });
            $(document).on('click', '.clear_filter_button', function(){
                $(".filter_form select").val('');
                $(".filter_form").submit();
            });

            $(function(){
                $('.timepicker').timepicker({
                    timeFormat: 'h:mm p',
                    interval: 15,
                    // defaultTime: '12 am',
                    dynamic: false,
                    dropdown: true,
                    scrollbar: true
                });
            });

            $(document).on("click", ".delete_button_in_listing", function(){
                $href = $(this).attr('data-href');
                $title = $(this).attr('data-title');
                $body_text = $(this).attr('data-body-text');
                $(".delete_modal_yes_link").attr('href', $href);
                $(".title_text").html($title);
                $(".body_text").html($body_text);
                $("#deleteModalPopup").modal('show');
            });
        </script>

        @include('admin.schedules.includes.view_schedule_js')

        @yield('script')

    </body>
</html>
