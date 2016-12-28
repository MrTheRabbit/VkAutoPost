<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>{{ $title }}</title>
	<link href="/css/bootstrap.css" rel="stylesheet">
	<link href="/css/app.css" rel="stylesheet">
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>
<body>
	<nav class="navbar navbar-inverse">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="{{ route('tasks::main') }}">ВК пост</a>
			</div>
				
			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<ul class="nav navbar-nav">
@if (strpos(Route::currentRouteName(), 'tasks::') !== false)
					<li class="active"><a href="{{ route('tasks::list') }}">Задачи <span class="sr-only">(current)</span></a></li>
@else
					<li><a href="{{ route('tasks::list') }}">Задачи</a></li>
@endif
@if (strpos(Route::currentRouteName(), 'groups::') !== false)
					<li class="active"><a href="{{ route('groups::list') }}">Группы ВК <span class="sr-only">(current)</span></a></li>
@else
					<li><a href="{{ route('groups::list') }}">Группы ВК</a></li>
@endif
@if (strpos(Route::currentRouteName(), 'log::') !== false)
					<li class="active"><a href="{{ route('log::index') }}">Логи <span class="sr-only">(current)</span></a></li>
@else
					<li><a href="{{ route('log::index') }}">Логи</a></li>
@endif
				</ul>
			</div>
		</div>
	</nav>


	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="page-header">
					<h1>{{ $title }}</h1>
				</div>
			
				@yield('content')
				
			</div>
		</div>
	</div>

	<script src="/js/jquery-3.1.1.js"></script>
	<script src="/js/bootstrap.js"></script>
	<script src="/js/underscore.js"></script>
	<script src="/js/backbone.js"></script>
	<script src="/js/application.js"></script>
</body>
</html>