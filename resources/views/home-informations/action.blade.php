@php $auth_user = auth()->user(); @endphp

<div class="d-flex align-items-center">
    {{-- Bouton Éditer --}}
    <a href="{{ route('home-informations.edit', $id) }}"
       class="btn btn-sm btn-success me-2"
       title="{{ __('message.update_form_title', ['form' => __('message.home_information')]) }}">
      ✏️
    </a>

    {{-- Bouton Supprimer --}}
    <a href="javascript:void(0)"
       class="btn btn-sm btn-danger"
       data--submit="hi{{ $id }}"
       data--confirmation="true"
       data-title="{{ __('message.delete_form_title', ['form' => __('message.home_information')]) }}"
       title="{{ __('message.delete_form_title', ['form' => __('message.home_information')]) }}"
       data-message="{{ __('message.delete_msg') }}">
      🗑️
    </a>

    {!! Form::open([
         'route'       => ['home-informations.destroy', $id],
         'method'      => 'delete',
         'data--submit'=> "hi{$id}"
    ]) !!}
    {!! Form::close() !!}
</div>
