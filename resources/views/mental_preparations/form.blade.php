@push('scripts')
<script>
    (function ($) {
        $(document).ready(function () {
            // TinyMCE uniquement sur la description
            tinymceEditor('.tinymce-description', ' ', function (ed) {}, 450);

            // Gérer l'affichage du champ vidéo (URL vs upload)
            var videoType = $('select[name=video_type]').val() || 'upload';
            toggleVideoField(videoType);

            $('select[name=video_type]').change(function() {
                toggleVideoField($(this).val());
            });

            // Gestion du champ prix selon le type de programme
            function togglePriceField() {
                var programType = $('#program_type').val();
                var priceField = $('#price_field');
                var priceInput = $('input[name="price"]');

                if (programType === 'paid') {
                    priceField.show();
                    priceInput.attr('required', true);
                } else {
                    priceField.hide();
                    priceInput.attr('required', false);
                    priceInput.val('');
                }
            }

            // Initialiser au chargement de la page
            togglePriceField();

            // Écouter les changements du select program_type
            $('#program_type').on('change', function() {
                togglePriceField();
            });
        });

        function toggleVideoField(type) {
            if (type === 'url') {
                $('.video_url').removeClass('d-none');
                $('.video_upload').addClass('d-none');
            } else {
                $('.video_upload').removeClass('d-none');
                $('.video_url').addClass('d-none');
            }
        }
    })(jQuery);
</script>
@endpush

<x-app-layout :assets="$assets ?? []">
  <div>
    @php $id = $id ?? null; @endphp

    @if($id)
      {!! Form::model(
            $data,
            [
              'route'     => ['mental-preparations.update', $id],
              'method'    => 'patch',
              'enctype'   => 'multipart/form-data'
            ]
          ) !!}
    @else
      {!! Form::open([
            'route'   => ['mental-preparations.store'],
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
          ]) !!}
    @endif

    <div class="card">
      <div class="card-header d-flex justify-content-between">
        <h4 class="card-title">{{ $pageTitle }}</h4>
        <div class="card-action">
          <a href="{{ route('mental-preparations.index') }}"
             class="btn btn-sm btn-primary">
            {{ __('message.back') }}
          </a>
        </div>
      </div>

      <div class="card-body">
        {{-- Ligne 1 : titre, slug, statut --}}
        <div class="row">
          <div class="form-group col-md-4">
            {{ Form::label('title', __('message.title').' <span class="text-danger">*</span>', ['class'=>'form-control-label'], false) }}
            {{ Form::text('title', old('title', $data->title ?? ''), ['class'=>'form-control','placeholder'=>__('message.title'),'required']) }}
          </div>
          <div class="form-group col-md-4">
            {{ Form::label('slug', __('Slug').' <span class="text-danger">*</span>', ['class'=>'form-control-label'], false) }}
            {{ Form::text('slug', old('slug', $data->slug ?? ''), ['class'=>'form-control','placeholder'=>__('Slug'),'required']) }}
          </div>
          <div class="form-group col-md-4">
            {{ Form::label('status', __('message.status').' <span class="text-danger">*</span>', ['class'=>'form-control-label'], false) }}
            {{ Form::select('status',['active'=>__('message.active'),'inactive'=>__('message.inactive')], old('status',$data->status ?? 'active'), ['class'=>'form-control select2js','required']) }}
          </div>
        </div>

        {{-- Ligne 2 : Type de programme et prix --}}
        <div class="row">
          <div class="form-group col-md-4">
            {{ Form::label('program_type', 'Type de programme <span class="text-danger">*</span>', ['class'=>'form-control-label'], false) }}
            {{ Form::select('program_type', [
                'free' => 'Gratuit',
                'premium' => 'Premium (Abonnement)',
                'paid' => 'Payant (Achat individuel)'
            ], old('program_type', $data->program_type ?? 'free'), ['class'=>'form-control select2js','required','id'=>'program_type']) }}
          </div>
          <div class="form-group col-md-4" id="price_field" style="display: none;">
            {{ Form::label('price', 'Prix (€) <span class="text-danger">*</span>', ['class'=>'form-control-label'], false) }}
            {{ Form::number('price', old('price', $data->price ?? ''), ['placeholder'=>'Prix en euros','class'=>'form-control','step'=>'0.01','min'=>'0']) }}
          </div>
          <div class="col-md-4">
            {{-- Espace pour alignement --}}
          </div>
        </div>

        {{-- Ligne 3 : image et preview --}}
        <div class="row mt-3">
          <div class="form-group col-md-4">
            {{ Form::label('mental_image', __('message.image'), ['class'=>'form-control-label']) }}
            <input type="file" name="mental_image" accept="image/*" class="form-control file-input" />
          </div>
          @if(isset($id) && getMediaFileExit($data, 'mental_image'))
            <div class="col-md-2 position-relative mb-2">
              <img src="{{ getSingleMedia($data,'mental_image') }}" class="avatar-100 mt-1" alt="preview" />
              <a href="{{ route('remove.file',['id'=>$data->id,'type'=>'mental_image']) }}"
                 class="text-danger remove-file position-absolute top-0 end-0"
                 data--submit="confirm_form" data--confirmation="true" data--ajax="true"
                 title="{{ __('message.remove_file_title',['name'=>__('message.image')]) }}">&times;</a>
            </div>
          @endif
        </div>

        {{-- Ligne 4 : vidéo (type / url / upload / preview) --}}
        <div class="row mt-3">
          <div class="form-group col-md-4">
            {{ Form::label('video_type', __('message.video_type').' <span class="text-danger">*</span>', ['class'=>'form-control-label'], false) }}
            {{ Form::select('video_type',['upload'=>__('message.upload_video'),'url'=>__('message.video_url')], old('video_type',$data->video_type ?? 'upload'), ['class'=>'form-control select2js video_type','required']) }}
          </div>
          <div class="form-group col-md-4 video_url d-none">
            {{ Form::label('video_url', __('message.video_url'), ['class'=>'form-control-label']) }}
            {{ Form::url('video_url', old('video_url',$data->video_url ?? ''), ['class'=>'form-control','placeholder'=>__('message.video_url')]) }}
          </div>
          <div class="form-group col-md-4 video_upload">
            {{ Form::label('mental_video', __('message.video'), ['class'=>'form-control-label']) }}
            <input type="file" name="mental_video" accept="video/*" class="form-control file-input" />
          </div>
          @if(isset($id) && getMediaFileExit($data, 'mental_video'))
            <div class="col-md-2 position-relative mb-2">
              @php
                $url = getSingleMedia($data,'mental_video');
                $ext = pathinfo($url, PATHINFO_EXTENSION);
                $isImage = in_array(strtolower($ext), config('constant.IMAGE_EXTENTIONS', []));
              @endphp
              @if($isImage)
                <img src="{{ $url }}" class="avatar-100 mt-1" alt="preview" />
              @else
                <img src="{{ asset('images/file.png') }}" class="avatar-100 mt-1" alt="file icon" />
                <a href="{{ $url }}" download>{{ __('message.download') }}</a>
              @endif
              <a href="{{ route('remove.file',['id'=>$data->id,'type'=>'mental_video']) }}"
                 class="text-danger remove-file position-absolute top-0 end-0"
                 data--submit="confirm_form" data--confirmation="true" data--ajax="true"
                 title="{{ __('message.remove_file_title',['name'=>__('message.video')]) }}">&times;</a>
            </div>
          @endif
        </div>

        {{-- Description --}}
        <div class="row mt-3">
          <div class="form-group col-md-12">
            {{ Form::label('description', __('message.description'), ['class'=>'form-control-label']) }}
            {{ Form::textarea('description', old('description',$data->description ?? ''), ['class'=>'form-control tinymce-description','placeholder'=>__('message.description')]) }}
          </div>
        </div>
      </div>

      <div class="card-footer text-end">
        {{ Form::submit(__('message.save'), ['class'=>'btn btn-primary']) }}
      </div>
    </div>

    {!! Form::close() !!}
  </div>
</x-app-layout>
