{{--ZABLOKOWANE DLA KADRY - KONT Z MOŻLIWOŚCIĄ ZGŁASZANIA PROBLEMÓW--}}
@if($link->link == 'add_notification')
<li class="dropdown">
    <a class="dropdown-toggle" data-toggle="dropdown" href="#" id="cadre_support">
        <i class="fa fa-bell fa-fw" ></i>
        <span id="show_notification_cadre_count" class="fa-stack fa-5x has-badge" data-count="" style="
        visibility: hidden;
        width:  0em !important;
        height:  0em !important;
        top: -23px;
        left: 8px;">
        </span>
        <i class="fa fa-caret-down"></i>
    </a>
    <ul id="cadre_notifications" class="dropdown-menu dropdown-alerts" style="width: 80vh; overflow-y:scroll; max-height: 500px; margin-right: -200px">
    </ul>
</li>
@endif
