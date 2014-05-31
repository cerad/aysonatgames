/* ===========================================================
 * Group all my js functions into the Cerad namespace
 */
Cerad = {};

Cerad.alert = function(msg)
{
    alert('A Cerad Alert: ' + msg);
};
/* =====================================================
 * This turns all the checkboxes in a given named group
 * On or Off
 * 31 May 2014
 * 
 * This was developed under jquery 1.8.3
 * I used attr('checked') to determine if the checkbox was checked
 * Turns out that this was changed way back in 1.6.
 * prop shuld be used.
 * 
 * This function stopped working when upgrading to jquery 1.10
 * Changed attr to prop and all was well
 * 
 * Also at one point had names like refSchedSearchData[ages][All]
 * Now we get form[ages][]
 * Not sure if having elements with the same name is good.
 * Need to look at
 */
Cerad.checkboxAll = function(e)
{   
    var nameRoot = $(this).attr('name'); // "refSchedSearchData[ages][]";
        
    nameRoot = nameRoot.substring(0,nameRoot.lastIndexOf('['));
    
    var group = 'input[type=checkbox][name^="' + nameRoot + '"]';
    
    // attr return undefined if not set, 'checked' if it is
    var checked = $(this).prop('checked') ? true : false;
        
    $(group).attr('checked', checked);
};
/* ==================================
 * Used to have some date processing here
 * Not used anymore
 * See the s1games20131203 app for the code
 */


