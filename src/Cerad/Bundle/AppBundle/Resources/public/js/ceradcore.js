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
 */
Cerad.checkboxAll = function(e)
{
    var nameRoot = $(this).attr('name'); // "refSchedSearchData[ages][All]";
        
    nameRoot = nameRoot.substring(0,nameRoot.lastIndexOf('['));
    
    var group = 'input[type=checkbox][name^="' + nameRoot + '"]';
    
    // attr return undefined if not set, 'checked' if it is
    var checked = $(this).attr('checked') ? true : false;
        
    $(group).attr('checked', checked);
};
/* ====================================================
 * A method for translating year/nomth/day checkboxes
 * Into a readable date string
 * I think it has been replaced with datepicker
 */
Cerad.dateGen = function(e)
{   
    var nameChanged = $(this).attr('name'); // "refSchedSearchData[date1][month]";
        
    var nameRoot = nameChanged.substring(0,nameChanged.lastIndexOf('['));
    
    var year  = $('select[name="' + nameRoot + '[year]"]').val();
    var month = $('select[name="' + nameRoot + '[month]"]').val();
    var day   = $('select[name="' + nameRoot + '[day]"]').val();
    
    var desc  = $('input[name="' + nameRoot + '[date]"]');
   
    var date  = new Date(year,month-1,day);
    
    // Replace using datepicker if decide to always include it
    desc.val(date.toDateString('M d yy D'));
    
    //alert('Year ' + date);
};
/* ==================================================
 * This gets triggered (by datepicker) when the desc field has changed
 * this = desc field
 * Sets the year/month/day elements
 * 
 * Not real pretty but seems to get the job done
 */
Cerad.dateChanged = function(dateText) // 20120109
{
    // Just in case
    if (dateText.length !== 8) return;
    
    var nameChanged = $(this).attr('name'); // "refSchedSearchData[date1][month]";
    
    var nameRoot = nameChanged.substring(0,nameChanged.lastIndexOf('['));
    
    var day   = dateText.substring( 6, 8);
    var year  = dateText.substring( 0, 4);
    var month = dateText.substring( 4, 6);

    // console.log(nameRoot + ' ' + dateText + ' ' + year);
    
    $('select[name="' + nameRoot +   '[day]"]').val(day);
    $('select[name="' + nameRoot +  '[year]"]').val(year);
    $('select[name="' + nameRoot + '[month]"]').val(month);
    return;  
};



