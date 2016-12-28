@extends('master')

@section('content')

<div class="panel panel-primary">
	<div class="panel-heading">Задания</div>
	<div class="panel-body">
		<p><a class="btn btn-primary" href="{{ route('tasks::add') }}" role="button">Добавить задание</a></p>
	</div>

	<table class="table">
		<thead>
			<tr>
				<th>ID</th>
				<th>Время</th>
				<th>Группа</th>
				<th>Расширения</th>
				<!--<th>Путь</th>-->
				<th>Статус</th>
				<th>Действия</th>
			</tr>
		</thead>
		<tbody>
@foreach($tasks as $task)
<?
		$task->time_hh = $task->time_mm = 0;
		if (isset($task->time)) {
			$task->time_hh = floor($task->time / 60);
			$task->time_mm = $task->time % 60;
		}//\\ if
		if (strlen($task->time_hh) == 1) $task->time_hh = '0'.$task->time_hh;
		if (strlen($task->time_mm) == 1) $task->time_mm = '0'.$task->time_mm;
		
		$task->time_g = Date::today()->getTimestamp() + ($task->time * 60);
?>
			<tr>
				<td>{{ $task->id }}</td>
				<td>{{ $task->time_hh.':'.$task->time_mm }}</td>
				<td>{{ $all_groups[$task->group_id] }}</td>
				<td>
@if ($task->type_files === 'img')
  jpg, jpeg, png
@elseif ($task->type_files === 'gif')
  gif
@endif				
				</td>
				<!--<td>{{ $task->patch }}</td>-->
				<td>
@if ($task->time_g <= time())
@if ($task->is_error === 'Y')
					<span class="label label-danger">Ошибка</span>
@elseif ($task->is_error === 'N')
					<span class="label label-success">Выполнено</span>
@endif				
					{{ $task->status }}
@endif				
				</td>
				<td>
					<div class="btn-group btn-group-sm" role="group" aria-label="Small button group">
							<a class="btn btn-warning" href="{{ route('tasks::edit', [$task->id]) }}" role="button">Редактировать</a>
							<a class="btn btn-danger btn_group_delete" href="{{ route('tasks::delete', [$task->id]) }}" role="button">Удалить</a>
							<!--<button type="button" class="btn btn-danger btn_group_delete" data-id="{{ $task->id }}">Удалить</button>-->
					</div>
				</td>
			</tr>
@endforeach
		</tbody>
	</table>
</div>


@endsection