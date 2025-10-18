@push('scripts')
<script>
  (function($){
    $(document).ready(function(){
      // TinyMCE uniquement sur description (comme dans CategoryDiet)
      tinymceEditor('.tinymce-description',' ', function (ed) {}, 300);
    });
  })(jQuery);
</script>
@endpush

<x-app-layout :assets="$assets ?? []">
  <div>
    {{-- Si $id existe, on fait la mise à jour, sinon création --}}
    @php $id = $id ?? null; @endphp

    @if($id)
      {!! Form::model($data, [
            'route'  => ['goal-challenges.update', $id],
            'method' => 'patch'
          ]) !!}
    @else
      {!! Form::open(['route' => 'goal-challenges.store']) !!}
    @endif

      <div class="card">
        <div class="card-header d-flex justify-content-between">
          <div class="header-title">
            <h4 class="card-title">{{ $pageTitle }}</h4>
          </div>
          <div class="card-action">
            <a href="{{ route('goal-challenges.index') }}" class="btn btn-sm btn-secondary">
              {{ __('message.back') }}
            </a>
          </div>
        </div>

        <div class="card-body">
          <div class="row">
            {{-- Champ “theme” --}}
            <div class="col-md-4">
              {{ Form::label('theme', __('Theme'), [], false) }}
              {{ Form::select(
                  'theme',
                  ['physique'=>'Physique', 'alimentaire'=>'Alimentaire', 'mental'=>'Mental'],
                  old('theme', $data->theme ?? null),
                  ['class'=>'form-control', 'required']
              ) }}
            </div>

            {{-- Champ “title” --}}
            <div class="col-md-4">
              {{ Form::label('title', __('Title'), [], false) }}
              {{ Form::text(
                  'title',
                  old('title', $data->title ?? ''),
                  ['class'=>'form-control', 'required']
              ) }}
            </div>

            {{-- Champ “status” --}}
            <div class="col-md-4">
              {{ Form::label('status', __('Status'), [], false) }}
              {{ Form::select(
                  'status',
                  ['active'=>'Active', 'inactive'=>'Inactive'],
                  old('status', $data->status ?? 'active'),
                  ['class'=>'form-control', 'required']
              ) }}
            </div>
          </div>

          <div class="row mt-3">
            {{-- Champ “valid_from” --}}
            <div class="col-md-6">
              {{ Form::label('valid_from', __('Valid From'), [], false) }}
              {{ Form::date(
                  'valid_from',
                  old('valid_from', $data->valid_from ?? null),
                  ['class'=>'form-control', 'required']
              ) }}
            </div>

            {{-- Champ “valid_until” --}}
            <div class="col-md-6">
              {{ Form::label('valid_until', __('Valid Until'), [], false) }}
              {{ Form::date(
                  'valid_until',
                  old('valid_until', $data->valid_until ?? null),
                  ['class'=>'form-control', 'required']
              ) }}
            </div>
          </div>

          <div class="row mt-3">
            {{-- Champ “description” (TinyMCE) --}}
            <div class="col-12">
              {{ Form::label('description', __('message.description'), ['class'=>'form-control-label']) }}
              {{ Form::textarea(
                  'description',
                  old('description', $data->description ?? ''),
                  ['class'=>'form-control tinymce-description']
              ) }}
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
