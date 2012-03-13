$(document).ready(function(){
  $("#news_submit").click(function() {
   $("#news-slogan").remove();
   $("#news-input").remove();
   $("#newsletter").append("Votre email a bien été enregistré, vous recevrez prochainement notre newsletter");
  });
});