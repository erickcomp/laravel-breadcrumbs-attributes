@foreach ($crumbs as $crumb)
    @if ($crumb->url && !$loop->last)
        <li class="{{ $class }}">
            <a href="{{ $crumb->url }}">{{ $crumb->label }}</a>
        </li>
    @else
        <li @class([$class, $activeClass => $loop->last])>
            {{ $crumb->label }}
        </li>
    @endif
@endforeach
