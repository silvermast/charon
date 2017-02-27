var profileApp=new Vue({el:"#locker-app",data:{loader:!0,success:"",error:"",profile:{changePass1:"",changePass2:""},timeouts:{}},created:function(){var scope=this;$.get({url:"/profile",success:function(result){var decData=AES.decrypt(result),decObj=json_decode(decData);scope.profile=$.extend(scope.profile,decObj),scope.toggleLoader(!1)}})},computed:{passwordVerify:function(){return 0===this.profile.changePass1.length||this.profile.changePass1.length>12},passwordsMatch:function(){return 0===this.profile.changePass1.length||this.profile.changePass1===this.profile.changePass2}},methods:{clearMessages:function(){this.error=this.success=""},saveObject:function(){var scope=this;return scope.passwordsMatch?(scope.clearMessages(),scope.toggleLoader(!0),void $.post({url:"/profile",data:json_encode(AES.encrypt(scope.profile)),success:function(result){result=AES.decrypt(result),result=json_decode(result),scope.success="Successfully updated your profile!",scope.profile=$.extend(scope.profile,result),scope.toggleLoader(!1)},error:function(jqXHR){scope.error=jqXHR.responseText,scope.toggleLoader(!1)}})):void(scope.error="Passwords do not match.")},toggleLoader:function(toggle){var self=this;toggle?self.timeouts.loader=setTimeout(function(){self.loader=!0},200):(self.loader=!1,clearTimeout(self.timeouts.loader),window.scrollTo(0,0))}}});