@push('scripts')
<script>
(function($) {
    $(document).ready(function() {

        var resetSequenceNumbers = function() {
            $("#table_list tbody tr").each(function(i) {
                $(this).find('td:first').text(i + 1);
            });
        };
        resetSequenceNumbers();

        function initSelect2($elem) {
            var row = $elem.data('row');
            $elem.select2({
                width: "100%",
                tags: true,
                escapeMarkup: function(markup) { return markup; },
                templateSelection: function(data, container) {
                    if (!data.id) return data.text;
                    var html = '<span>' + data.text + '</span>';
                    html += ' <button type="button" class="btn btn-xs btn-primary set-btn" data-row="'+ row +'" data-token="'+ data.id +'">Set</button>';
                    return html;
                }
            });
        }
        
        $(".select2tagsjs").each(function(){
            initSelect2($(this));
        });
        
        tinymceEditor('.tinymce-description',' ',function(ed){},450);

        var row = 0;
        $('#add_button').on('click', function () {
            $(".select2tagsjs").select2("destroy");
            var tableBody = $('#table_list').find("tbody");
            var trLast = tableBody.find("tr:last");
            trLast.find(".removebtn").show().fadeIn(300);
            var trNew = trLast.clone();
            row = parseInt(trNew.attr('row'));
            row++;
            trNew.attr('id','row_'+row).attr('data-id',0).attr('row',row);
            trNew.find('[type="hidden"]').val(0).attr('data-id',0);
            trNew.find('[id^="workout_days_id_"]').attr('name',"workout_days_id["+row+"]").attr('id',"workout_days_id_"+row).val('');
            trNew.find('[id^="exercise_ids_"]')
                .attr('name',"exercise_ids["+row+"][]")
                .attr('id',"exercise_ids_"+row)
                .attr('data-row', row)
                .val('');
            trNew.find('[id^="is_rest_no_"]').attr('name',"is_rest["+row+"]").attr('id',"is_rest_no_"+row).val('0');
            trNew.find('[id^="is_rest_yes_"]').attr('name',"is_rest["+row+"]").attr('id',"is_rest_yes_"+row).val('1').prop('checked', false);
            trNew.find('[id^="remove_"]').attr('id',"remove_"+row).attr('row',row);
            // Pour les nouveaux rows, on ne génère pas de champ setsData ici.
            trLast.after(trNew);
            $(".select2tagsjs").each(function(){
                initSelect2($(this));
            });
            resetSequenceNumbers();
        });
        
        $(document).on('click','.removebtn', function() {
            var row = $(this).attr('row');
            var delete_row  = $('#row_'+row);
            var check_exists_id = delete_row.attr('data-id');
            var total_row = $('#table_list tbody tr').length;
            var user_response = confirm("{{ __('message.delete_msg') }}");
            if(!user_response) {
                return false;
            }
            if(total_row == 1){
                $('#add_button').trigger('click');
            }
            if(check_exists_id != 0 ) {
                $.ajax({
                    url: "{{ route('workoutdays.exercise.delete') }}",
                    type: 'post',
                    data: {'id': check_exists_id, '_token': $('input[name=_token]').val()},
                    dataType: 'json',
                    success: function (response) {
                        if(response['status']) {
                            delete_row.remove();
                            showMessage(response.message);
                        } else {
                            errorMessage(response.message);
                        }
                    }
                });
            } else {
                delete_row.remove();
            }
            resetSequenceNumbers();
        });

        // Suppression d'une ligne dans le modal des sets
        $(document).on('click', '.remove-set-row', function(){
            $(this).closest('tr').remove();
        });

        $(document).on('click', '.set-btn', function(e){
            e.preventDefault();
            e.stopPropagation();

            var rowId = $(this).data('row');
            var tokenId = $(this).data('token');

            $('#modalSets').data('row-id', rowId).data('token', tokenId);
            $('#tableSets tbody').empty();

            // Récupérer les sets enregistrés pour cet exercice du row
            var setsData = $('input[name="sets['+ rowId +']['+ tokenId +']"]').val();
            
            if (setsData) {
                var sets = JSON.parse(setsData);
                $.each(sets, function(i, set){
                    var tr = '<tr>'+
                        '<td><input type="number" name="reps[]" class="form-control" min="0" value="'+(set.reps || '')+'" placeholder="{{ __("message.reps") }}"></td>'+
                        '<td><input type="number" name="time[]" class="form-control" min="0" value="'+(set.time || '')+'" placeholder="{{ __("message.time") }}"></td>'+
                        '<td><input type="number" name="weight[]" class="form-control" min="0" value="'+(set.weight || '')+'" placeholder="{{ __("message.weight") }}"></td>'+
                        '<td><input type="number" name="rest[]" class="form-control" min="0" value="'+(set.rest || '')+'" placeholder="{{ __("message.rest") }}"></td>'+
                        '<td><button type="button" class="btn btn-danger btn-sm remove-set-row">Supprimer</button></td>'+
                    '</tr>';
                    $('#tableSets tbody').append(tr);
                });
            } else {
                var tr = '<tr>'+
                    '<td><input type="number" name="reps[]" class="form-control" min="0" placeholder="{{ __("message.reps") }}"></td>'+
                    '<td><input type="number" name="time[]" class="form-control" min="0" placeholder="{{ __("message.time") }}"></td>'+
                    '<td><input type="number" name="weight[]" class="form-control" min="0" placeholder="{{ __("message.weight") }}"></td>'+
                    '<td><input type="number" name="rest[]" class="form-control" min="0" placeholder="{{ __("message.rest") }}"></td>'+
                    '<td><button type="button" class="btn btn-danger btn-sm remove-set-row">Supprimer</button></td>'+
                '</tr>';
                $('#tableSets tbody').append(tr);
            }

            $('#modalSets').modal('show');
        });
        
        $('#addSet').on('click', function(){
            var newRow = '<tr>'+
                '<td><input type="number" name="reps[]" class="form-control" min="0" placeholder="{{ __('message.reps') }}"></td>'+
                '<td><input type="number" name="time[]" class="form-control" min="0" placeholder="{{ __('message.time') }}"></td>'+
                '<td><input type="number" name="weight[]" class="form-control" min="0" placeholder="{{ __('message.weight') }}"></td>'+
                '<td><input type="number" name="rest[]" class="form-control" min="0" placeholder="{{ __('message.rest') }}"></td>'+
                '<td><button type="button" class="btn btn-danger btn-sm remove-set-row">Supprimer</button></td>'+
            '</tr>';
            $('#tableSets tbody').append(newRow);
        });
        
        $('#saveSets').on('click', function(){
            var rowId = $('#modalSets').data('row-id');
            var tokenId = $('#modalSets').data('token');
            var sets = [];
            $('#tableSets tbody tr').each(function(){
                var reps = $(this).find('input[name="reps[]"]').val();
                var time = $(this).find('input[name="time[]"]').val();
                var weight = $(this).find('input[name="weight[]"]').val();
                var rest = $(this).find('input[name="rest[]"]').val();
                sets.push({reps: reps, time: time, weight: weight, rest: rest});
            });
            var inputId = 'sets_data_' + rowId + '_' + tokenId;
            // Si le champ n'existe pas, le créer et l'ajouter dans le row correspondant
            if($('#' + inputId).length === 0){
                $('<input>').attr({
                    type: 'hidden',
                    id: inputId,
                    name: 'sets['+ rowId +']['+ tokenId +']',
                    class: 'setsData'
                }).appendTo('#row_' + rowId);
            }
            // Met à jour le champ avec les sets (ancien + nouveau)
            $('#' + inputId).val(JSON.stringify(sets));
            $('#modalSets').modal('hide');
        });
        
        function showMessage(message) {
            Swal.fire({
                icon: 'success',
                title: "{{ __('message.done') }}",
                text: message,
                confirmButtonColor: "var(--bs-primary)",
                confirmButtonText: "{{ __('message.ok') }}"
            });
        }
        function errorMessage(message) {
            Swal.fire({
                icon: 'error',
                title: "{{ __('message.opps') }}",
                text: message,
                confirmButtonColor: "var(--bs-primary)",
                confirmButtonText: "{{ __('message.ok') }}"
            });
        }

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
})(jQuery);
</script>
@endpush

<x-app-layout :assets="$assets ?? []">
<div>
    <?php $id = $id ?? null; ?>
    @if(isset($id))
        {!! Form::model($data, [ 'route' => [ 'workout.update', $id], 'method' => 'patch', 'enctype' => 'multipart/form-data' ]) !!}
    @else
        {!! Form::open(['route' => ['workout.store'], 'method' => 'post', 'enctype' => 'multipart/form-data' ]) !!}
    @endif
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">{{ $pageTitle }}</h4>
                    </div>
                    <div class="card-action">
                        <a href="{{ route('workout.index') }}" class="btn btn-sm btn-primary" role="button">{{ __('message.back') }}</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-4">
                            {{ Form::label('title', __('message.title').' <span class="text-danger">*</span>', [ 'class' => 'form-control-label' ], false) }}
                            {{ Form::text('title', old('title'), [ 'placeholder' => __('message.title'),'class' =>'form-control','required']) }}
                        </div>
                        <div class="form-group col-md-4">
                            {{ Form::label('level_id', __('message.level').' <span class="text-danger">*</span>', [ 'class' => 'form-control-label' ], false) }}
                            {{ Form::select('level_id', isset($id) ? [ optional($data->level)->id => optional($data->level)->title ] : [], old('level_id'), [
                                'class' => 'select2js form-group level',
                                'data-placeholder' => __('message.select_name',[ 'select' => __('message.level') ]),
                                'data-ajax--url' => route('ajax-list', ['type' => 'level']),
                                'required'
                            ]) }}
                        </div>
                        <div class="form-group col-md-4">
                            {{ Form::label('workout_type_id', __('message.workouttype').' <span class="text-danger">*</span>', [ 'class' => 'form-control-label' ], false) }}
                            {{ Form::select('workout_type_id', isset($id) ? [ optional($data->workouttype)->id => optional($data->workouttype)->title ] : [], old('workout_type_id'), [
                                'class' => 'select2js form-group workouttype',
                                'data-placeholder' => __('message.select_name',[ 'select' => __('message.workouttype') ]),
                                'data-ajax--url' => route('ajax-list', ['type' => 'workout_type']),
                                'required'
                            ]) }}
                        </div>
                        <div class="form-group col-md-4">
                            {{ Form::label('status', __('message.status').' <span class="text-danger">*</span>', ['class'=>'form-control-label'], false) }}
                            {{ Form::select('status', [ 'active' => __('message.active'), 'inactive' => __('message.inactive') ], old('status'), [ 'class' => 'form-control select2js', 'required']) }}
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-control-label" for="image">{{ __('message.image') }}</label>
                            <div>
                                <input class="form-control file-input" type="file" name="workout_image" accept="image/*">
                            </div>
                        </div>
                        @if(isset($id) && getMediaFileExit($data, 'workout_image'))
                        <div class="col-md-2 mb-2 position-relative">
                            <img id="workout_image_preview" src="{{ getSingleMedia($data,'workout_image') }}" alt="workout-image" class="avatar-100 mt-1">
                            <a class="text-danger remove-file" href="{{ route('remove.file', ['id' => $data->id, 'type' => 'workout_image']) }}"
                               data--submit="confirm_form"
                               data--confirmation="true"
                               data--ajax="true"
                               data-toggle="tooltip"
                               title="{{ __("message.remove_file_title", ["name" => __("message.image")]) }}"
                               data-title="{{ __("message.remove_file_title", ["name" => __("message.image")]) }}"
                               data-message="{{ __("message.remove_file_msg") }}">
                                <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
                                    <path opacity="0.4" d="M16.34 1.99976H7.67C4.28 1.99976 2 4.37976 2 7.91976V16.0898C2 19.6198 4.28 21.9998 7.67 21.9998H16.34C19.73 21.9998 22 19.6198 22 16.0898V7.91976C22 4.37976 19.73 1.99976 16.34 1.99976Z" fill="currentColor"></path>
                                    <path d="M15.0158 13.7703L13.2368 11.9923L15.0148 10.2143C15.3568 9.87326 15.3568 9.31826 15.0148 8.97726C14.6728 8.63326 14.1198 8.63426 13.7778 8.97626L11.9988 10.7543L10.2198 8.97426C9.87782 8.63226 9.32382 8.63426 8.98182 8.97426C8.64082 9.31626 8.64082 9.87126 8.98182 10.2123L10.7618 11.9923L8.98582 13.7673C8.64382 14.1093 8.64382 14.6643 8.98582 15.0043C9.15682 15.1763 9.37982 15.2613 9.60382 15.2613C9.82882 15.2613 10.0518 15.1763 10.2228 15.0053L11.9988 13.2293L13.7788 15.0083C13.9498 15.1793 14.1728 15.2643 14.3968 15.2643C14.6208 15.2643 14.8448 15.1783 15.0158 15.0083C15.3578 14.6663 15.3578 14.1123 15.0158 13.7703Z" fill="currentColor"></path>
                                </svg>
                            </a>
                        </div>
                        @endif
                        <div class="form-group col-md-4">
                            {{ Form::label('program_type', 'Type de programme <span class="text-danger">*</span>', ['class' => 'form-control-label'], false) }}
                            {{ Form::select('program_type', [
                                'free' => 'Gratuit',
                                'premium' => 'Premium (Abonnement)',
                                'paid' => 'Payant (Achat individuel)'
                            ], old('program_type'), ['class' => 'form-control select2js', 'required', 'id' => 'program_type']) }}
                        </div>
                        <div class="form-group col-md-4" id="price_field" style="display: none;">
                            {{ Form::label('price', 'Prix (€) <span class="text-danger">*</span>', ['class' => 'form-control-label'], false) }}
                            {{ Form::number('price', old('price'), ['placeholder' => 'Prix en euros', 'class' => 'form-control', 'step' => '0.01', 'min' => '0']) }}
                        </div>
                        <div class="form-group col-md-4">
                            {{ Form::label('is_monthly_program', 'Programme du mois', ['class' => 'form-control-label']) }}
                            <div>
                                {!! Form::hidden('is_monthly_program',0, null, ['class' => 'form-check-input']) !!}
                                {!! Form::checkbox('is_monthly_program',1, null, ['class' => 'form-check-input']) !!}
                                <label class="custom-control-label" for="is_monthly_program"></label>
                            </div>
                        </div>
                        <div class="form-group col-md-12">
                            {{ Form::label('description', __('message.description'), ['class' => 'form-control-label']) }}
                            {{ Form::textarea('description', null, ['class'=> 'form-control tinymce-description', 'placeholder'=> __('message.description')]) }}
                        </div>
                    </div>
                    <hr>
                    <h5 class="mb-3">{{ __('message.workout_days') }} <button type="button" id="add_button" class="btn btn-sm btn-primary float-end">{{ __('message.add', ['name' => '']) }}</button></h5>
                    <div class="row">
                        <div class="col-md-12">
                            <table id="table_list" class="table workout_days_table table-responsive">
                                <thead>
                                    <tr>
                                        <th class="col-md-1">#</th>
                                        <th class="col-md-3">{{ __('message.exercise') }}</th>
                                        <th class="col-md-3">{{ __('message.is_rest') }}</th>
                                        <th class="col-md-2">{{ __('message.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @if(isset($id) && count($data->workoutDay) > 0)
                                    @foreach($data->workoutDay as $key => $field)
                                        <tr id="row_{{ $key }}" row="{{ $key }}" data-id="{{ $field->id }}">
                                            <td></td>
                                            <td>
                                                <div class="form-group" id="exercise_ids_{{ $key }}">
                                                    <input type="hidden" name="workout_days_id[{{ $key }}]" class="form-control" value="{{ $field->id }}" id="workout_days_id_{{ $key }}" />
                                                    {{ Form::select('exercise_ids['.$key.'][]', $field->exercise_data ?? [], $field->exercise_ids ?? old('exercise_ids'), [
                                                        'class' => 'select2tagsjs form-group exercise',
                                                        'multiple' => 'multiple',
                                                        'id' => 'exercise_ids_'.$key,
                                                        'data-row' => $key,
                                                        'data-placeholder' => __('message.select_name', ['select' => __('message.exercise')]),
                                                        'data-ajax--url' => route('ajax-list', ['type' => 'exercise']),
                                                    ]) }}
                                                </div>
                                            </td>
                                            
                                            <td>
                                                <div class="form-group">
                                                    <input type="hidden" name="is_rest[{{ $key }}]" value="0" id="is_rest_no_{{ $key }}">
                                                    {!! Form::checkbox('is_rest['.$key.']', 1, $field->is_rest ?? null, ['class' => 'form-check-input', 'id' => 'is_rest_yes_'.$key]) !!}
                                                </div>
                                            </td>
                                            <td>
                                                <a href="javascript:void(0)" class="editSets btn btn-sm btn-icon btn-primary" row="{{ $key }}">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <a href="javascript:void(0)" id="remove_{{ $key }}" class="removebtn btn btn-sm btn-icon btn-danger" row="{{ $key }}">
                                                    <span class="btn-inner">
                                                        <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
                                                            <path d="M19.3248 9.46826C19.3248 9.46826 18.7818 16.2033 18.4668 19.0403C18.3168 20.3953 17.4798 21.1893 16.1088 21.2143C13.4998 21.2613 10.8878 21.2643 8.27979 21.2093C6.96079 21.1823 6.13779 20.3783 5.99079 19.0473C5.67379 16.1853 5.13379 9.46826 5.13379 9.46826" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M20.708 6.23975H3.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M17.4406 6.23973C16.6556 6.23973 15.9796 5.68473 15.8256 4.91573L15.5826 3.69973C15.4326 3.13873 14.9246 2.75073 14.3456 2.75073H10.1126C9.53358 2.75073 9.02558 3.13873 8.87558 3.69973L8.63258 4.91573C8.47858 5.68473 7.80258 6.23973 7.01758 6.23973" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                </a>
                                                {{-- Si des sets sont enregistrés pour ce jour, générer un champ caché par exercice --}}
                                                @if(isset($field->sets_data) && is_array($field->sets_data) && count($field->sets_data) > 0)
                                                    @foreach($field->sets_data as $exerciseId => $json)
                                                        <input type="hidden" name="sets[{{ $key }}][{{ $exerciseId }}]" 
                                                               id="sets_data_{{ $key }}_{{ $exerciseId }}" 
                                                               class="setsData" 
                                                               value="{{ is_array($json) ? json_encode($json) : $json }}">
                                                    @endforeach
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr id="row_0" row="0" data-id="0">
                                        <td></td>
                                        <td>
                                            <div class="form-group" id="exercise_ids_0">
                                                <input type="hidden" name="workout_days_id[0]" class="form-control" value="0" id="workout_days_id_0" />
                                                {{ Form::select('exercise_ids[0][]', [], old('exercise_ids'), [
                                                    'class' => 'select2tagsjs form-group exercise',
                                                    'multiple' => 'multiple',
                                                    'id' => 'exercise_ids_0',
                                                    'data-row' => 0,
                                                    'data-placeholder' => __('message.select_name', ['select' => __('message.exercise')]),
                                                    'data-ajax--url' => route('ajax-list', ['type' => 'exercise']),
                                                ]) }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group">
                                                <input type="hidden" name="is_rest[0]" value="0" id="is_rest_no_0">
                                                {!! Form::checkbox('is_rest[0]', 1, old('is_rest'), ['class' => 'form-check-input', 'id' => 'is_rest_yes_0']) !!}
                                            </div>
                                        </td>
                                        <td>
                                            <a href="javascript:void(0)" class="editSets btn btn-sm btn-icon btn-primary" row="0">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <a href="javascript:void(0)" id="remove_0" class="removebtn btn btn-sm btn-icon btn-danger" row="0">
                                                <span class="btn-inner">
                                                    <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
                                                        <path d="M19.3248 9.46826C19.3248 9.46826 18.7818 16.2033 18.4668 19.0403C18.3168 20.3953 17.4798 21.1893 16.1088 21.2143C13.4998 21.2613 10.8878 21.2643 8.27979 21.2093C6.96079 21.1823 6.13779 20.3783 5.99079 19.0473C5.67379 16.1853 5.13379 9.46826 5.13379 9.46826" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M20.708 6.23975H3.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M17.4406 6.23973C16.6556 6.23973 15.9796 5.68473 15.8256 4.91573L15.5826 3.69973C15.4326 3.13873 14.9246 2.75073 14.3456 2.75073H10.1126C9.53358 2.75073 9.02558 3.13873 8.87558 3.69973L8.63258 4.91573C8.47858 5.68473 7.80258 6.23973 7.01758 6.23973" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    </svg>
                                                </span>
                                            </a>
                                            <input type="hidden" name="sets[0]" id="sets_data_0" class="setsData" value="">
                                        </td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                            <!-- Modal pour définir les sets -->
<!-- Modal pour définir les sets -->
<div class="modal fade" id="modalSets" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">  <!-- Ajout de modal-lg ici -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Définir les sets</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table" id="tableSets">
                    <thead>
                        <tr>
                            <th>{{ __('message.reps') }}</th>
                            <th>{{ __('message.time') }}</th>
                            <th>{{ __('message.weight') }}</th>
                            <th>{{ __('message.rest') }}</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ Form::number('reps[]', old('reps'), [ 'placeholder' => __('message.reps'), 'class' =>'form-control form-control-lg', 'min' => 0 ]) }}</td>
                            <td>{{ Form::number('time[]', old('time'), [ 'placeholder' => __('message.time'), 'class' =>'form-control form-control-lg', 'min' => 0 ]) }}</td>
                            <td>{{ Form::number('weight[]', old('weight'), [ 'placeholder' => __('message.weight'), 'class' =>'form-control form-control-lg', 'min' => 0 ]) }}</td>
                            <td>{{ Form::number('rest[]', old('rest'), [ 'placeholder' => __('message.rest'), 'class' =>'form-control form-control-lg', 'min' => 0 ]) }}</td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm remove-set-row">Supprimer</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <button id="addSet" type="button" class="btn btn-secondary">Ajouter un set</button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="saveSets">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

                        </div>
                    </div>
                    <hr>
                    {{ Form::submit( __('message.save'), ['class' => 'btn btn-md btn-primary float-end']) }}
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
</div>
</x-app-layout>
