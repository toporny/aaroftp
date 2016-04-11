<script type="text/javascript">

	bootbox.dialog({
		message: "{!! $message !!}.",
		backdrop: true,
		title: '<p class="text-{!! $class !!}">{!! $title !!}</p>',
		buttons: {
		ok: {
			label: "{!! $label !!}",
			className: "btn-{!! $class !!}",
			callback: function() {
				window.location = '{{url()}}/bucketfile';
			}
		}
		}
	});

</script>

