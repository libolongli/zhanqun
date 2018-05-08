<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>站群管理</title>
    <link rel="shortcut icon" href="favicon.ico"> <link href="css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="css/ui.jqgrid.css?0820" rel="stylesheet">
    <link href="css/style.css?v=4.1.0" rel="stylesheet">
    <style>
        /* Additional style to fix warning dialog position */

        #alertmod_table_list_2 {
            top: 900px !important;
        }
    </style>
</head>

<body class="gray-bg">
    <div class="wrapper wrapper-content  animated fadeInRight">
        <div class="row">
            <div class="col-sm-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="jqGrid_wrapper">
                            <table id="table_list_2"></table>
                            <div id="pager_list_2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 全局js -->
    <script src="js/jquery.min.js?v=2.1.4"></script>
    <!-- jqGrid -->
    <script src="js/grid.locale-cn.js?0820"></script>
    <script src="js/jquery.jqGrid.min.js?0821"></script>
    <!-- 自定义js -->
    <script>
        $(document).ready(function () {

            $.jgrid.defaults.styleUI = 'Bootstrap';
            // Examle data for jqGrid
            // var mydata = ;

            // Configuration for jqGrid Example 2
            $("#table_list_2").jqGrid({
                url:'/sites.php?type=get',
                datatype: "json",
                height: 450,
                autowidth: true,
                shrinkToFit: true,
                rowNum: 20,
                rowList: [1000],
                colNames: ['ID','域名', '站点名称', '模板','操作'],
                colModel: [
                    {
                        name: 'id',
                        index: 'id',
                        editable: false,
                        width: 20
                    },
                    {
                        name: 'domain',
                        index: 'domain',
                        editable: true,
                        width: 200
                    },
                    {
                        name: 'title',
                        index: 'title',
                        editable: true,
                        width: 200
                    },
                    {
                        name: 'template',
                        index: 'template',
                        editable: true,
                        width: 200,
                        edittype:"select",
                        editoptions:{value:"default:默认模板"}
                    },
                    {
                        name: 'op',
                        index: 'op',
                        editable: false,
                        width: 100
                    }
                ],
                pager: "#pager_list_2",
                viewrecords: true,
                caption: "默认后台管理员用户名/密码 <span style='color:red'>(admin/123qwert)</span>",
                multiselect:true,  //多选
                add: true,
                edit: true,
                addtext: '添加站点',
                edittext: '修改站点',
                editurl:'sites.php?type=edit',
                hidegrid: false
            });

            // Add selection
            $("#table_list_2").setSelection(4, true);


            // Setup buttons
            $("#table_list_2").jqGrid('navGrid', '#pager_list_2', {
                edit: false,
                add: true,
                del: true,
                search: false
            },{  //修改(添加/删除)的时候的参数
                // height: 150,
                reloadAfterSubmit: true,
                top:300,
                left:600,
                afterSubmit:function(resposedata){
                    var data = JSON.parse(resposedata.responseText);
                    if(!data.status){
                        alert(data.msg);
                    }
                    return [true,''];   //必须要返回
                },
                closeAfterEdit:true
            },{
                top:300,
                left:600,
                closeAfterAdd:true,
                afterSubmit:function(resposedata){
                    var data = JSON.parse(resposedata.responseText);
                    if(!data.status){
                        alert(data.msg);
                    }
                    return [true,''];
                }
            },{
                top:300,
                left:700,
                afterSubmit:function(resposedata){
                    var data = JSON.parse(resposedata.responseText);
                    if(!data.status){
                        alert(data.msg);
                    }
                    return [true,''];
                }
            }
            );

            // Add responsive to jqGrid
            $(window).bind('resize', function () {
                var width = $('.jqGrid_wrapper').width();
                $('#table_list_2').setGridWidth(width);
            });
        });
    </script>

</body>

</html>
