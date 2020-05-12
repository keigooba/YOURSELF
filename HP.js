
window.addEventListener('DOMContentLoaded', //ドムを取得するとき
  function(){

    var node =  document.getElementById('count-text'); //idの定義

    node.addEventListener('keyup',function(){ //キーを押したとき

      var count = this.value.length; //カウントの取得

      var counterNode = document.querySelector('.show-count-text'); //classの定義

      counterNode.innerText = count;


    },false);


  },false
);
//バリデーション
$(function(){

  const MSG_TEXT_MAX = '※5文字以内';
  const MSG_EMPTY = '※入力必須';
  const MSG_PHONE_TEXT_MAX = '※20文字以内';
  const MSG_EMIL_TYPE = '※emailの形式ではありません。';
  const MSG_PHONE_TYPE = '※数字で入力してください。';
  const MSG_TEXTAREA_MAX = '※150文字以内で入力してください。';

$(".valid-text").keyup(function(){

  var form_g = $(this).closest('.form-group');

  if($(this).val().length > 5){
    form_g.removeClass('has-success').addClass('has-error');
    form_g.find('.help-block').text(MSG_TEXT_MAX);
  }else{
    form_g.removeClass('has-error').addClass('has-success');
    form_g.find('.help-block').text('');
  }
});

$(".valid-email").keyup(function(){

  var form_g = $(this).closest('.form-group');

  if($(this).val().length === 0){
    form_g.removeClass('has-success').addClass('has-error');
    form_g.find('.help-block').text(MSG_EMPTY);
  }else if($(this).val().length > 50 || !$(this).val().match(/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/) ){
    form_g.removeClass('has-success').addClass('has-error');
    form_g.find('.help-block').text(MSG_EMIL_TYPE);
  }else{
    form_g.removeClass('has-error').addClass('has-success');
    form_g.find('.help-block').text('');
  }
});
$(".valid-tel").keyup(function(){

  var form_g = $(this).closest('.form-group');

  if($(this).val().length > 20){
    form_g.removeClass('has-success').addClass('has-error');
    form_g.find('.help-block').text(MSG_PHONE_TEXT_MAX);
  }else{
    form_g.removeClass('has-error').addClass('has-success');
    form_g.find('.help-block').text('');
  }
});
$(".valid-textarea").keyup(function(){

  var form_g = $(this).closest('.form-group');

  if($(this).val().length === 0){
    form_g.removeClass('has-success').addClass('has-error');
    form_g.find('.help-block').text(MSG_EMPTY);
  } else if($(this).val().length > 150){
    form_g.removeClass('has-success').addClass('has-error');
    form_g.find('.help-block').text(MSG_TEXTAREA_MAX);
  } else{
    form_g.removeClass('has-error').addClass('has-success');
    form_g.find('.help-block').text('');
  }
});

});
$(function(){

$(".format-number").change(function(){

  var format_before = $(this).val();

  format_before = format_before.replace(/-/g,''); //はいふんを削除する

  var format_after = format_before.replace(/[Ａ-Ｚａ-ｚ０-９]/g,function(s){ return String.fromCharCode(s.charCodeAt(0)-0xFEE0) });


  if(format_after.length === 11){

    $(this).val(format_after.substr(0,3)+'-'+format_after.substr(3,4)+'-'+format_after.substr(7,4));
  }else{

    $(this).val(format_after);
  }
});

});