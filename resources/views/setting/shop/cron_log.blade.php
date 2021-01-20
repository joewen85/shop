@extends('layouts.base')
@section('content')
@section('title', trans('队列日志打印'))
<style>
    .panel{
        margin-bottom:10px!important;
        padding-left: 20px;
        border-radius: 10px;
    }
    .panel .active a {
        background-color: #29ba9c!important;
        border-radius: 18px!important;
        color:#fff;
    }
    .panel a{
        border:none!important;
        background-color:#fff!important;
    }
    .content{
        background: #eff3f6;
        padding: 10px!important;
    }
    .con{
        padding-bottom:20px;
        border-radius: 8px;
        min-height:100vh;
        background-color:#fff;
        position:relative;
    }
    .con .setting .block{
        padding:10px;
        background-color:#fff;
        border-radius: 8px;
        margin-bottom:10px;
    }
    .con .setting .block .title{
        font-size:18px;
        margin-bottom:15px;
        display:flex;
        align-items:center;
    }
    .el-form-item{
        padding-left:300px;
        margin-bottom:10px!important;
    }
    .el-form-item__label{
        margin-right:30px;
    }
    .confirm-btn{
        width: 100%;
        position:absolute;
        bottom:0;
        left:0;
        line-height:63px;
        background-color: #ffffff;
        box-shadow: 0px 8px 23px 1px
        rgba(51, 51, 51, 0.3);
        background-color:#fff;
        text-align:center;
    }
    b{
        font-size:14px;
    }
</style>
<div id='re_content' >
    @include('layouts.newTabs')
    <div class="con">
        <div class="setting">
            <div class="block">
                <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>队列日志</b></div>
                {!! $data !!}
            </div>

        </div>
    </div>
</div>
<script>
    var vm = new Vue({
        el: "#re_content",
        delimiters: ['[[', ']]'],
        data() {

        },
        mounted () {
        },

    });
</script>
@endsection
