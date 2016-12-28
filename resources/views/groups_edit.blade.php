@extends('master')

@section('content')

<form method="POST" action="{{ $form_action }}">
	{{ csrf_field() }}
	<div class="form-group">
		<label for="f_title">Название группы ВК</label>
		<input type="text" class="form-control" id="f_title" name="f_title" value="{{ $group->title or '' }}" disabled>
	</div>

	<div class="form-group">
		<label for="f_app_id">ID приложения вконтакте</label>
		<input type="text" class="form-control" id="f_app_id" name="f_app_id" value="{{ $group->app_id or '' }}">
	</div>
	
	<div class="form-group">
		<label for="f_api_secret">Секретный код приложения</label>
		<input type="text" class="form-control" id="f_api_secret" name="f_api_secret" value="{{ $group->api_secret or '' }}">
	</div>
	
	<div class="form-group">
		<label for="f_access_token">Токен доступа</label>
		<div class="input-group">
			<input type="text" class="form-control" id="f_access_token" name="f_access_token" value="{{ $group->access_token or '' }}">
			<span class="input-group-btn">
				<a class="btn btn-default" href="{{ $authorize_url or '#' }}" role="button">Получить токен</a>
			</span>
		</div>
	</div>

	<div class="form-group">
		<label for="f_api_settings">Права доступа приложения</label>
		<select multiple class="form-control" id="f_api_settings" name="f_api_settings[]">
@foreach($list_api_settings as $api_settings_sid => $api_settings_value)
			<option value="{{ $api_settings_sid }}"{{ (isset($group->api_settings) && in_array($api_settings_sid, $group->api_settings)) ? ' selected' : '' }}>{{ $api_settings_value }}</option>
@endforeach
		</select>

	</div>
	
	<button type="submit" class="btn btn-success">Сохранить</button>
</form>


@endsection