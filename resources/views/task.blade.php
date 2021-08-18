<p>
	{{ $assignee }} you have a {{ $task->title }}, task ahead by {{ $assignor }}.
	@if($task->deadline) the due date is {{ $task->deadline }}.
	@else There is no as such deadline. 
	@endif
	<a href="http://localhost:8000/api/viewtask/{{ $task->id }}">Click here </a> to view details.
	Thank you.
</p>