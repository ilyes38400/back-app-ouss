@push('scripts')
<script>
(function($){
  $(function(){
    // Pas de toggle nécessaire ici
  });
})(jQuery);
</script>
@endpush

<x-app-layout :assets="$assets ?? []">
  <div class="container py-4">
    @php $id = $id ?? null; @endphp

    @if($id)
      {!! Form::model(
            $data,
            [
              'route'   => ['home-informations.update', $id],
              'method'  => 'patch',
              'enctype' => 'multipart/form-data'
            ]
          ) !!}
    @else
      {!! Form::open([
            'route'   => ['home-informations.store'],
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
          ]) !!}
    @endif

    <div class="card">
      {{-- En-tête --}}
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">{{ $pageTitle }}</h4>
        <a href="{{ route('home-informations.index') }}" class="btn btn-sm btn-secondary">
          {{ __('message.back') }}
        </a>
      </div>

      {{-- Corps --}}
      <div class="card-body">
        <div class="row gy-3">
          {{-- Titre --}}
          <div class="col-md-6">
            {{ Form::label('title', __('message.title').' <span class="text-danger">*</span>', ['class'=>'form-label'], false) }}
            {{ Form::text('title', old('title',$data->title??''), [
                'class'=>'form-control','required'
            ]) }}
          </div>
          {{-- Upload vidéo --}}
          <div class="col-md-6">
            {{ Form::label('home_video', __('message.video').' <span class="text-danger">*</span>', ['class'=>'form-label'], false) }}
            <input type="file"
                   name="home_video"
                   class="form-control"
                   accept="video/*"
                   {{ $id ? '' : 'required' }} />
          </div>

          {{-- Aperçu si on édite --}}
          @if(isset($id) && getMediaFileExit($data,'home_video'))
            <div class="col-md-12 mt-3">
              @php $url = getSingleMedia($data,'home_video'); @endphp
              <video src="{{ $url }}" controls class="w-100 rounded mb-2"></video>
              <a href="{{ route('remove.file',['id'=>$data->id,'type'=>'home_video']) }}"
                 class="btn btn-sm btn-outline-danger"
                 data--submit="confirm_form"
                 data--confirmation="true"
                 data--ajax="true">
                {{ __('message.remove_file_title',['name'=>__('message.video')]) }}
              </a>
            </div>
          @endif
        </div>
      </div>

      {{-- Pied --}}
      <div class="card-footer text-end">
        {{ Form::submit(__('message.save'), ['class'=>'btn btn-primary']) }}
      </div>
    </div>

    {!! Form::close() !!}
  </div>
</x-app-layout>
