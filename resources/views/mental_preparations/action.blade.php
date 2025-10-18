@php $auth_user = auth()->user(); @endphp

<div class="d-flex align-items-center">
    <a href="{{ route('mental-preparations.edit', $id) }}"
       class="btn btn-sm btn-success me-2"
       title="{{ __('message.update_form_title', ['form' => __('message.mental_preparation')]) }}">
      ✏️
    </a>
    <a href="javascript:void(0)"
       class="btn btn-sm btn-danger"
       data--submit="mp{{ $id }}"
       data--confirmation="true"
       data-title="{{ __('message.delete_form_title',['form'=>__('message.mental_preparation')]) }}"
       title="{{ __('message.delete_form_title',['form'=>__('message.mental_preparation')]) }}"
       data-message="{{ __('message.delete_msg') }}">
      🗑️
    </a>
    {!! Form::open([
         'route'      => ['mental-preparations.destroy', $id],
         'method'     => 'delete',
         'data--submit'=> "mp{$id}"
      ]) !!}
    {!! Form::close() !!}
</div>
