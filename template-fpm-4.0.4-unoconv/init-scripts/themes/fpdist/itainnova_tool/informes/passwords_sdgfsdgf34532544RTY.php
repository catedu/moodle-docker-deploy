<?php
require_once('libreria/checkAdmin.php');
$title = "Contrase&ntilde;as";

$PAGE->set_url(new moodle_url('/itainnova_tool/passwords.php'));
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_cacheable(false);
$PAGE->navbar->ignore_active();
$PAGE->navbar->add('ITAINNOVA Tools', new moodle_url('/itainnova_tool'));
$PAGE->navbar->add($title, new moodle_url(substr($PAGE->url,0,strpos($PAGE->url,'?'))));
$PAGE->set_pagelayout('admin');
echo $OUTPUT->header();
$get = optional_param('course', 0, PARAM_INT);

$sql_get_users_and_plain_pass = "SELECT DISTINCT(user.id),  user.username, user.firstname, user.lastname, user.email, ppropio.ppropio
FROM {course} course
INNER JOIN {context} mContext ON mContext.instanceid = course.id
INNER JOIN {role_assignments} role_assignments ON mContext.id = role_assignments.contextid
INNER JOIN {role} role ON role.id = role_assignments.roleid
INNER JOIN {user} user ON user.id = role_assignments.userid
JOIN ppropio ON ppropio.idd = user.id
WHERE role.id != :roleid";
if($get!=0) $sql_get_users_and_plain_pass.=" AND course.id = :course";

$entradas = $DB->get_records_sql($sql_get_users_and_plain_pass,array('roleid'=>6,'course'=>$get));
$courses = get_courses();
?>
<div class="row">
  <div class="span2 offset1">
    <label>Selecciona el curso:</label>
  </div>
  <div class="span7">
  <form method="get">
  <select name="course" onchange="this.form.submit()" style="width:90%">
    <option value='0' <?=($get==0)?'selected':''?>>Todos</option>
    <?php foreach ($courses as $course):?>
      <option value="<?=$course->id?>" <?=($get==$course->id)?'selected':''?>><?=$course->fullname?></option>
    <?php endforeach;?>
  </select>
</form>
</div>
</div>
<div class="row">
  <script src='libreria/datatables/datatables.min.js'></script>
  <link rel="stylesheet" type="text/css" href="libreria/datatables/datatables.min.css">
  <table id="example" class="display" style="width:100%">
    <thead>
      <tr>
        <th>UserID</th>
        <th>Username</th>
        <th>Nombre</th>
        <th>Apellidos</th>
        <th>Correo</th>
        <th>Contrase&ntilde;a</th>
        <th>Ver</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($entradas as $usuario):?>
        <tr>
          <td><?=$usuario->id?></td>
          <td><?=$usuario->username?></td>
          <td><?=$usuario->firstname?></td>
          <td><?=$usuario->lastname?></td>
          <td><?=$usuario->email?></td>
          <td><input id="<?=$usuario->id?>" type="password" value="<?=$usuario->ppropio?>" readonly></td>
          <td><button onclick="change(<?=$usuario->id?>)" class="btn btn-default" ><i class="fa fa-eye"></i></button></td>
        </tr>
      <?php endforeach;?>
    </tbody>
    <tfoot>
      <tr>
        <td>UserID</td>
        <th>Username</th>
        <td>Nombre</td>
        <td>Apellidos</td>
        <td>Correo</td>
        <td>Contrase&ntilde;a</td>
        <td>Ver</td>
      </tr>
    </tfoot>
  </table>
</div>
<script type="text/javascript" charset="utf-8">
var table;
$(document).ready(function() {
  // Setup - add a text input to each footer cell
  $('#example tfoot td').each( function () {
    var title = $(this).text();

    if(title!='<?=html_entity_decode('Contrase&ntilde;a')?>' && title!='Ver')
    $(this).html( '<input type="text" placeholder="Buscar por '+title+'" />' );
    else $(this).html('');
  } );
  table = $('#example').DataTable({
    "lengthMenu": [[10,15,20, 50, 100, -1], [10,15,20, 50, 100, "Todas las entradas"]],
  });


  // Apply the search
  table.columns().every( function () {
    var that = this;
    $( 'input', this.footer() ).on( 'keyup change', function () {
      if ( that.footer() !== this.value ) {
        that
        .search( this.value )
        .draw();
      }
    } );
  } );

} );

// For demo to fit into DataTables site builder...
$('#example')
.removeClass( 'display' )
.addClass('table table-striped table-bordered');

</script>
<script type="text/javascript" charset="utf-8">
function change(id){
  var input = $('#'+id);
  input.attr('type',(input.attr('type')=='password' ? 'text' : 'password'));
}
</script>
<?php
echo $OUTPUT->footer();
?>
