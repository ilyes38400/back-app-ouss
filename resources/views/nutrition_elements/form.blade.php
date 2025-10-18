@push('scripts')
<script>
    (function($) {
        $(document).ready(function(){
            // TinyMCE uniquement sur description
            tinymceEditor('.tinymce-description',' ', function (ed) {}, 450);
        });
    })(jQuery);
</script>
@endpush

<x-app-layout :assets="$assets ?? []">
    <div>
        @php $id = $id ?? null; @endphp

        @if(isset($id))
            {!! Form::model($data, [
                    'route' => ['nutrition-elements.update', $id],
                    'method' => 'patch',
                    'enctype' => 'multipart/form-data'
                ]) !!}
        @else
            {!! Form::open([
                    'route' => ['nutrition-elements.store'],
                    'method' => 'post',
                    'enctype' => 'multipart/form-data'
                ]) !!}
        @endif

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title">{{ $pageTitle }}</h4>
                        </div>
                        <div class="card-action">
                            <a href="{{ route('nutrition-elements.index') }}" class="btn btn-sm btn-primary">
                                {{ __('message.back') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">

                        {{-- Title --}}
                        <div class="row">
                            <div class="form-group col-md-6">
                                {{ Form::label('title', __('message.title').' <span class="text-danger">*</span>', ['class'=>'form-control-label'], false) }}
                                {{ Form::text('title', old('title', $data->title ?? ''), [
                                        'placeholder' => __('message.title'),
                                        'class' => 'form-control',
                                        'required'
                                    ]) }}
                            </div>

                            {{-- Slug --}}
                            <div class="form-group col-md-6">
                                {{ Form::label('slug', __('Slug').' <span class="text-danger">*</span>', ['class'=>'form-control-label'], false) }}
                                {{ Form::text('slug', old('slug', $data->slug ?? ''), [
                                        'placeholder' => __('Slug'),
                                        'class' => 'form-control',
                                        'required'
                                    ]) }}
                            </div>
                        </div>

                        {{-- Status & Image --}}
                        <div class="row">
                            <div class="form-group col-md-4">
                                {{ Form::label('status', __('message.status').' <span class="text-danger">*</span>', ['class'=>'form-control-label'], false) }}
                                {{ Form::select('status', [
                                        'active'   => __('message.active'),
                                        'inactive' => __('message.inactive')
                                    ], old('status', $data->status ?? 'active'), [
                                        'class' => 'form-control select2js',
                                        'required'
                                    ]) }}
                            </div>
                            <div class="form-group col-md-4">
                                {{ Form::label('image', __('message.image'), ['class'=>'form-control-label']) }}
                                <input type="file" name="image" accept="image/*" class="form-control file-input" />
                            </div>
                            @if(isset($id) && getMediaFileExit($data, 'image'))
                                <div class="col-md-2 mb-2 position-relative">
                                    <img src="{{ getSingleMedia($data, 'image') }}" alt="" class="avatar-100 mt-1"/>
                                    <a href="{{ route('remove.file', ['id'=>$data->id,'type'=>'image']) }}"
                                       class="text-danger remove-file position-absolute top-0 end-0"
                                       data--submit="confirm_form" data--confirmation="true" data--ajax="true"
                                       title="{{ __('message.remove_file_title',['name'=>__('message.image')]) }}">
                                        &times;
                                    </a>
                                </div>
                            @endif
                        </div>

                        {{-- Description (seul champ WYSIWYG) --}}
                        <div class="row">
                            <div class="form-group col-md-12">
                                {{ Form::label('description', __('message.description'), ['class'=>'form-control-label']) }}
                                {!! Form::textarea('description', old('description', $data->description ?? ''), [
                                    'class'       => 'form-control tinymce-description',
                                    'placeholder' => __('message.description')
                                ]) !!}
                            </div>
                        </div>

                        <hr>
                        {{ Form::submit(__('message.save'), ['class'=>'btn btn-md btn-primary float-end']) }}
                    </div>
                </div>
            </div>
        </div>

        {!! Form::close() !!}
    </div>
</x-app-layout>
