@extends('master')

@section('content')

<form method="POST" action="{{ $form_action }}">
	{{ csrf_field() }}
	<div class="form-group">
		<label for="f_group_id">Приложение</label>
		<select class="form-control" name="f_group_id" id="f_group_id">
			<option value="0"></option>
@foreach($list_groups as $itemGroup)
			<option value="{{ $itemGroup->id }}"{{ (isset($task->group_id) && $itemGroup->id == $task->group_id) ? ' selected' : '' }}>{{ $itemGroup->title }}</option>
@endforeach
		</select>
	</div>
	
	<div class="form-group">
		<label for="f_group_id">Действие</label>
		<select class="form-control" name="f_action" id="f_action">
			<option value="0"></option>
@for ($i = 1; $i <= count($list_actions); $i++)
			<option value="{{ $i }}"{{ (isset($task->action) && $task->action == $i) ? ' selected' : '' }}>{{ $list_actions[$i] }}</option>
@endfor
		</select>
	</div>

	<div class="form-group hide show_for_action_2">
		<label for="f_user_id">ID пользователя</label>
		<input type="text" class="form-control" id="f_user_id" name="f_user_id" value="{{ $task->user_id or '' }}">
	</div>

	<div class="form-group hide show_for_action_2">
		<label for="f_group_id">Альбом пользователя</label>
		<input type="text" class="form-control" id="f_folder_id" name="f_folder_id" value="{{ $task->folder_id or '' }}">
	</div>

	<div class="form-group">
		<label for="f_time_hh">Время размещения</label>

		<div class="row">
			<div class="col-xs-2">
				<select class="form-control" id="f_time_hh" name="f_time_hh">
@for ($i = 0; $i <= 23; $i++)
					<option value="{{ $i }}"{{ (isset($task->time_hh) && $task->time_hh == $i) ? ' selected' : '' }}>{{ (strlen($i) == 1) ? '0'.$i : $i }}</option>
@endfor
				</select> 
			</div>
			<div class="col-xs-2">
				<select class="form-control" id="f_time_mm" name="f_time_mm">
@for ($i = 0; $i <= 59; $i++)
					<option value="{{ $i }}"{{ (isset($task->time_mm) && $task->time_mm == $i) ? ' selected' : '' }}>{{ (strlen($i) == 1) ? '0'.$i : $i }}</option>
@endfor
				</select>
			</div>
		</div>

	</div>

	<div class="form-group">
		<label for="f_cnt">Количество вложений</label>
		<select class="form-control" id="f_cnt" name="f_cnt">
@for ($i = 1; $i <= 10; $i++)
			<option value="{{ $i }}"{{ (isset($task->cnt) && $task->cnt == $i) ? ' selected' : '' }}>{{ $i }}</option>
@endfor
		</select>
	</div>

	<div class="form-group">
		<label for="f_patch">Папка вложений</label>
		<div class="input-group">
			<input type="text" class="form-control" id="f_patch" name="f_patch" value="{{ $task->patch or '' }}">
			<span class="input-group-btn">
				<button class="btn btn-default" type="button" data-toggle="modal" data-target="#modal_patch">
					<span class="glyphicon glyphicon-option-horizontal" aria-hidden="true"></span>
				</button>
			</span>
		</div>
	</div>

	<div class="form-group">
		<label for="f_type_files">Расширение вложений</label>
		<select multiple class="form-control" id="f_type_files" name="f_type_files">
			<option value="img"{{ (isset($task->type_files) && $task->type_files == 'img') ? ' selected' : '' }}>jpg, jpeg, png</option>
			<option value="gif"{{ (isset($task->type_files) && $task->type_files == 'gif') ? ' selected' : '' }}>gif</option>
		</select>
	</div>
	
	<button type="submit" class="btn btn-success">Сохранить</button>
</form>


<div class="modal fade" id="modal_patch" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Добавить папку</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<div class="input-group">
						<input type="text" class="form-control" id="f_modal_patch" name="f_modal_patch" value="{{ $task->patch or '/' }}">
					</div>
				</div>

				<div class="f_modal_list_folders">
					<table class="table table-striped">
						<tbody>
@foreach($list_root_patch as $strPatch)
							<tr><td><a href="#" data-patch="/{{ $strPatch }}">{{ $strPatch }}</a></td></tr>
@endforeach
						</tbody>
					</table>
				</div>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
				<button type="button" class="btn btn-primary">Добавить</button>
			</div>
		</div>
	</div>
</div>
@endsection