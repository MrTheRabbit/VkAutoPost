@extends('master')

@section('content')

<div class="panel panel-primary">
	<table class="table">
		<thead>
			<tr>
				<th>ID</th>
				<th>Дата</th>
				<th>Текст</th>
				<th>Данные</th>
			</tr>
		</thead>
		<tbody>
@foreach($logs as $log)
<?
$strClass = '';
if ($log->level_name == 'INFO') $strClass = 'info';
elseif ($log->level_name == 'ERROR') $strClass = 'danger';

if (strlen($strClass)) $strClass = ' class="pastel_'.$strClass.'"';

$date = new Date($log->created_at);
$log->created_at = $date->format('H:i:s d.m.Y');

?>
			<tr{!! $strClass !!}>
				<td>{{ $log->id }}</td>
				<td>{{ $log->created_at }}</td>
				<td>{{ $log->message }}</td>
				<td><div class="block_overflow"><small class="block_overflow">{{ $log->context }}</small></div></td>
			</tr>
@endforeach
		</tbody>
	</table>
</div>


@endsection