    layui.use(['form','layer'],function () {
        var layer = layui.layer,form = layui.form;
        var form,province,city,district;
        var is_first_select = true;
        //获取地区数据
        $.get(district_url,function(data){
            province = data.result[0];
            city = data.result[1];
            district = data.result[2];
            get_province();
        });
        //省份切换事件
        form.on('select(province)',function (data) {
            var id = $(data.elem).find("option:selected").attr("data-id");
            if(id == undefined){
                $("#city").html('');
                $("#district").html('');
                form.render('select');
                return ;
            }
            toggle_province($('#province option:selected').data('id'));
        });
        //城市切换事件
        form.on('select(city)',function (data) {
            var id = $(data.elem).find("option:selected").attr("data-id");
            var provinceid = $(data.elem).find("option:selected").attr("data-provinceid");
            toggle_city(id,provinceid);
        });
        //获取省份
        function get_province(){
            let select_index = -2;
            for (let x in province) {
                let selected = '';
                if(is_first_select && province[x].fullname == select_province){
                    selected = 'selected';
                    select_index = x;
                }
                $("#province").append('<option ' + selected + ' data-id="' + x + '" value="' + province[x].fullname + '">' + province[x].fullname + '</option>');
            }
            form.render('select');
            toggle_province(select_index);
        }
        //切换省份
        function toggle_province(id) {
            //防止空数组报错
            if(id == '-2'){
                return ;
            }
            $("#city").html('');
            $("#district").html('');
            var i = province[id].cidx[0];
            var is_select = false;
            var select_index;
            $("#city").append('<option data-id="-2" value="">全部</option>');
            if(!city[i].hasOwnProperty('cidx')){
                let selected = '';
                if(province[id].fullname == select_city){
                    selected = 'selected';
                    is_select = true;
                    select_index = i;
                }
                $("#city").append('<option ' + selected + ' data-id = "-1" data-provinceid="' + i + '"  value="' + province[id].fullname + '">' + province[id].fullname + '</option>');
                toggle_city("-1",id);
                return ;
            }
            for(;i<=province[id].cidx[1];i++){
                let selected = '';
                if(is_first_select &&  city[i].fullname == select_city){
                    selected = 'selected';
                    is_select = true;
                    select_index = i;
                }
                $("#city").append('<option ' + selected + ' data-id="' + i + '"  value="' + city[i].fullname + '">' + city[i].fullname + '</option>');
            }
            if(is_select){
                toggle_city(select_index);
            }else{
                toggle_city("-2");
            }
            form.render('select');
        }
        //切换市区
        function toggle_city(id,provinceid) {
            $("#district").html('');
            $("#district").append('<option data-id="-2" value="">全部</option>');
            if(id == "-1"){
                let i = province[provinceid].cidx[0];
                for(;i<=province[provinceid].cidx[1];i++){
                    let selected = '';
                    if(is_first_select &&  city[i].fullname == select_district){
                        selected = 'selected';
                    }
                    $("#district").append('<option ' + selected + ' data-id="' + i + '"  value="' + city[i].fullname + '">' + city[i].fullname + '</option>');
                }
            }else if(id == "-2"){
                form.render('select');
            }else{
                let i = city[id].cidx[0];
                for(;i<=city[id].cidx[1];i++){
                    let selected = '';
                    if(is_first_select &&  district[i].fullname == select_district){
                        selected = 'selected';
                    }
                    $("#district").append('<option ' + selected + ' data-id="' + i + '"  value="' + district[i].fullname + '">' + district[i].fullname + '</option>');
                }
            }
            is_first_select = false;
            form.render('select');
        }
    });