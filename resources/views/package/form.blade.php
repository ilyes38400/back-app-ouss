@push('scripts')
    <script>
        (function($) {
            $(document).ready(function(){
                tinymceEditor('.tinymce-description',' ',function (ed) {
                }, 450)

            });
        })(jQuery);
    </script>
@endpush

<x-app-layout :assets="$assets ?? []">
    <div>
        <?php $id = $id ?? null;?>
        @if(isset($id))
            {!! Form::model($data, [ 'route' => ['package.update', $id], 'method' => 'patch', 'enctype' => 'multipart/form-data' ]) !!}
        @else
            {!! Form::open(['route' => ['package.store'], 'method' => 'package', 'enctype' => 'multipart/form-data' ]) !!}
        @endif
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title">{{ $pageTitle }}</h4>
                        </div>
                        <div class="card-action">
                            <a href="{{ route('package.index') }} " class="btn btn-sm btn-primary" role="button">{{ __('message.back') }}</a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-4">
                                {{ Form::label('name', __('message.name').' <span class="text-danger">*</span>',[ 'class' => 'form-control-label' ], false ) }}
                                {{ Form::text('name', old('name'),[ 'placeholder' => __('message.name'),'class' =>'form-control','required']) }}
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('duration_unit',__('message.duration_unit'), ['class' => 'form-control-label']) }}
                                {{ Form::select('duration_unit',[ 'monthly' => __('message.monthly'), 'yearly' => __('message.yearly') ], old('duration_unit'),[ 'class' =>'form-control select2js','required']) }}
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('duration',trans('message.duration').' <span class="text-danger">*</span>', ['class'=>'form-control-label'],false) }}
                                {{ Form::select('duration',['1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7', '8' => '8', '9' => '9', '10' => '10', '11' => '11', '12' => '12' ],old('duration'),[ 'id' => 'duration' ,'class' =>'form-control select2js','required']) }}
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('price', __('message.price').' <span class="text-danger">($)*</span>',['class'=>'form-control-label'], false ) }}
                                {{ Form::number('price', old('price'), ['class' => 'form-control',  'min' => 0, 'step' => 'any', 'required', 'placeholder' => __('message.price') ]) }}
                            </div>
                            <div class="form-group col-md-4">
                                {{ Form::label('status',__('message.status').' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                {{ Form::select('status',[ 'active' => __('message.active'), 'inactive' => __('message.inactive') ], old('status'), [ 'class' =>'form-control select2js','required']) }}
                            </div>
                            <div class="form-group col-md-12">
                                {{ Form::label('description',__('message.description'), ['class' => 'form-control-label']) }}
                                {{ Form::textarea('description', null, ['class'=> 'form-control tinymce-description' , 'placeholder'=> __('message.description') ]) }}
                            </div>
                        </div>
                        <hr>
                        {{ Form::submit( __('message.save'), ['class'=>'btn btn-md btn-primary float-end']) }}
                    </div>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
</x-app-layout>
