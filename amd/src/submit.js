// Standard license block omitted.
/*
 * @package    block_overview
 * @copyright  2015 Someone cool
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 /**
  * @module theme_solent2017/border
  */
define(['jquery', 'core/str', 'core/notification', 'report_ee/submit'], function($, str, notification) {

    return {
        init: function(admin) {
          $("input[name='locked']").click(function(){
            var lockedValue = $("input[name='locked']:checked").val();
            if(lockedValue == 1){
              var lockedWarning = str.get_strings([
                  {key: 'lockedwarning', component: 'report_ee'},
              ]);
              $.when(lockedWarning).done(function() {
                   $('.lockedwarning').text(M.util.get_string('lockedwarning', 'report_ee'));
                   $('.lockedwarning').addClass("alert alert-danger");
              }).fail(notification.exception);
            }else{
              $('.lockedwarning').text("");
              $(".lockedwarning").removeClass("alert alert-danger");
              if(admin == 1){
                $( "#id_locked" ).prop( "disabled", true );
              }
            }
          });
        }
    };
});
