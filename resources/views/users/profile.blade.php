@push('scripts')
{{ $dataTable->scripts() }}
    <script>
        function getAssignList(type = ''){
            url = "{{ route('get.assigndietlist') }}";
            if( type == 'workout' ) {
                url = "{{ route('get.assignworkoutlist') }}";
            }
            
            $.ajax({
                type: 'get',
                url: url,
                data: {
                    'user_id': "{{ $data->id }}",
                },
                success: function(res){
                    $('#'+type+'-data').html(res.data);
                }
            });
        }
        $(document).ready(function () {
            getAssignList('diet');
            getAssignList('workout');
        });
    </script>
@endpush
<x-app-layout :assets="$assets ?? []">
    <div class="row">
        <div class="col-lg-6">
            <div class="profile-content tab-content">
                <div id="profile-profile" class="">
                    <div class="card">
                        <div class="card-header">
                            <div class="header-title">
                                <h4 class="card-title">{{__('message.profile')}}</h4>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-start align-items-center">
                                <div class="pe-3">
                                    <img src="{{ getSingleMedia($data, 'profile_image') }}" class="rounded-pill avatar-130 img-fluid"  alt="user-img">
                                </div>
                                <div class="pe-3">
                                    <p class="m-0">{{ $data->display_name }}</p>
                                    <p class="m-0">{{ $data->email }}</p>
                                    <p class="m-0">{{ $data->phone_number }}</p>
                                    <p class="m-0">{{ $data->gender }}</p>
                                </div>
                            </div>
                            <p></p>
                            <div class="d-flex justify-content-between align-items-center flex-wrap  mb-2">
                                <div>
                                    <span>{{ __('message.weight') }}</span>
                                    <p>{{ optional($data->userProfile)->weight ?? '-' }} {{ optional($data->userProfile)->weight_unit }}</p>
                                </div>
                                <div>
                                    <span>{{ __('message.height') }}</span>
                                    <p>{{ optional($data->userProfile)->height ?? '-' }} {{ optional($data->userProfile)->height_unit }}</p>
                                </div>
                                <div>
                                    <span>{{ __('message.age') }}</span>
                                    <p>{{ optional($data->userProfile)->age ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">{{__('message.assigndiet')}}</h4>
                    </div>
                    <div class="text-center ms-3 ms-lg-0 ms-md-0">
                        <a href="#" class="float-end btn btn-sm btn-primary" data-modal-form="form" data-size="small"
                            data--href="{{ route('add.assigndiet', $data['id']) }}"
                            data-app-title="{{ __('message.add_form_title',['form' => __('message.assigndiet')]) }}"
                            data-placement="top">{{ __('message.add_form_title', ['form' => __('message.diet')] ) }}</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive mt-4 assign-profile-max-height">
                        <table id="basic-table" class="table table-striped mb-0" role="grid">
                            <thead>
                                <tr>
                                    <th>{{ __('message.image') }}</th>
                                    <th>{{ __('message.title') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="diet-data">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">{{__('message.assignworkout')}}</h4>
                    </div>
                    <div class="text-center ms-3 ms-lg-0 ms-md-0">
                        <a href="#" class="float-end btn btn-sm btn-primary" data-modal-form="form" data-size="small"
                            data--href="{{ route('add.assignworkout', $data['id']) }}"
                            data-app-title="{{ __('message.add_form_title',['form' => __('message.assignworkout')]) }}"
                            data-placement="top">{{ __('message.add_form_title', ['form' => __('message.workout')] ) }}</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive mt-4 assign-profile-max-height">
                        <table id="basic-table" class="table table-striped mb-0" role="grid">
                            <thead>
                                <tr>
                                    <th>{{ __('message.image') }}</th>
                                    <th>{{ __('message.title') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="workout-data">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-block">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title mb-0">{{ __('message.list_form_title', [ 'form' => __('message.subscription_history') ]) }}</h4>
                    </div>
                </div>
                <div class="card-body">
                    {{ $dataTable->table(['class' => 'table  w-100'],false) }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
