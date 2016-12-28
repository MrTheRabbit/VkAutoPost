@extends('master')

@section('content')

<div class="panel panel-primary">
  <div class="panel-heading">Группы ВК</div>
  <div class="panel-body">
    <p><a class="btn btn-primary" href="{{ route('groups::add') }}" role="button">Добавить группу</a></p>
  </div>

  <table class="table">
    <thead>
	<tr>
	    <th>ID</th>
	    <th>Название</th>
	    <th>Действия</th>
	</tr>
    </thead>
    <tbody>
@foreach($groups as $group)
			<tr>
				<td>{{ $group->id }}</td>
				<td>{{ $group->title }}</td>
				<td>
					<div class="btn-group btn-group-sm" role="group" aria-label="Small button group">
						<a class="btn btn-warning" href="{{ route('groups::edit', [$group->id]) }}" role="button">Редактировать</a>
						<a class="btn btn-danger btn_group_delete" href="{{ route('groups::delete', [$group->id]) }}" role="button">Удалить</a>
						<!--<button type="button" class="btn btn-danger btn_group_delete" data-id="{{ $group->id }}">Удалить</button>-->
					</div>
				</td>
			</tr>
@endforeach
		</tbody>
	</table>
</div>


@endsection