<?php
$servername = "localhost";
$username = "root";
$password = "15483";
$dbname = "health_check";

$conn = mysqli_connect($servername, $username, $password, $dbname);

$showButton = false; // مخفی کردن دکمه در ابت

if (isset($_POST['search'])) {
  $vid = $_POST['vid'];
  $visit_search = []; // پاکسازی اطلاعات قبلی

  $select_visit = $conn->prepare("CALL select_visit(?)");
  $select_visit->bind_param("i", $vid);
  $select_visit->execute();
  $select_visit_result = $select_visit->get_result();
  
  if ($select_visit_result-> num_rows > 0) {
    $visit_search = $select_visit_result->fetch_assoc();
    $showButton = true;
    }else{
      echo "<script>window.onload = function() { 
        Swal.fire({
            icon: 'error',
            title: 'شناسه وارد شده در بین ویزیت ها یافت نشد ... ابتدا ویزیت را ثبت کنید!',
            confirmButtonColor: '#28a745'
        });
    }</script>";
    }
  $select_visit->close();
}
  // ثبت یا ویرایش بیمار
    if (isset($_POST['save'])){
    $vid = $_POST['vid'];
    $ptid = $_POST['ptid'];
    $drid = $_POST['drid'];
    $date = $_POST['date'];

    if($vid == 0){
      $insert_visit = $conn->prepare("CALL insert_visit(?, ?, ?)");
    $insert_visit->bind_param("sss", $ptid, $drid, $date);
    
    try {
      $insert_visit->execute();
      echo "<script>window.onload = function() { 
          Swal.fire({
              icon: 'succ ness',
              title: 'ویزیت با موفقیت ثبت شد!',
              confirmButtonColor: '#28a745'
          });
      }</script>";
  } catch (mysqli_sql_exception $e) {
      echo "<script>window.onload = function() { 
          Swal.fire({
              icon: 'error',
              title: 'خطا',
              text: '" . $e->getMessage() . "',
              confirmButtonColor: '#d33'
          });
      }</script>";
  }
  $insert_visit->close();
    }else{
      echo "<script>window.onload = function() { 
        Swal.fire({
            icon: 'error',
            title: 'خطا',
            text: 'ویزیت قبلا ثبت شده است!',
            confirmButtonColor: '#d33'
        });
    }</script>";
    
}

        }

  if (isset($_POST['update'])) {
    $vid = $_POST['vid'];
    $ptid = $_POST['ptid'];
    $drid = $_POST['drid'];
    $date = $_POST['date'];

    if($vid == 0){
      echo "<script>window.onload = function() { 
        Swal.fire({
            icon: 'error',
            title: 'خطا',
            text: 'ویزیت قبلا ثبت نشده است!',
            confirmButtonColor: '#d33'
        });
    }</script>";
    
    }else{
    $update_visit = $conn->prepare("CALL update_visit(?, ?, ? ,?)");
    $update_visit->bind_param("isss", $vid, $ptid, $drid, $date);
    
    try {
      $update_visit->execute();
      echo "<script>window.onload = function() { 
          Swal.fire({
              icon: 'success',
              title: 'ویزیت با موفقیت ویرایش شد!',
              confirmButtonColor: '#28a745'
          });
      }</script>";
  } catch (mysqli_sql_exception $e) {
      echo "<script>window.onload = function() { 
          Swal.fire({
              icon: 'error',
              title: 'خطا',
              text: '" . $e->getMessage() . "',
              confirmButtonColor: '#d33'
          });
      }</script>";
  }
  $update_visit->close();
}

}
// حذف بیمار
if (isset($_POST['delete'])) {
  $vid = $_POST['vid'];

  $delete_visit = $conn->prepare("CALL delete_visit(?)");
  $delete_visit->bind_param("i", $vid);

    try {
      $delete_visit->execute();
      echo "<script>window.onload = function() { 
          Swal.fire({
              icon: 'success',
              title: 'اطلاعات با موفقیت حذف شدند!',
              confirmButtonColor: '#28a745'
          });
      }</script>";
  } catch (mysqli_sql_exception $e) {
      echo "<script>window.onload = function() { 
          Swal.fire({
              icon: 'error',
              title: 'خطا',
              text: '" . $e->getMessage() . "',
              confirmButtonColor: '#d33'
          });
      }</script>";
  }
    $delete_visit->close();
  
}


$conn->close();

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ثبت اطلاعات ویزیت | سامانه سلامت</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Vazirmatn:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <!-- لکه‌های بنفش پس‌زمینه -->
  <div class="bg-blob-circle blob-c1"></div>
  <div class="bg-blob-circle blob-c2"></div>

  <!-- نوار بالای فرم -->
  <div class="form-top-bar">
    <a href="menu.html" class="nav-btn-back" title="بازگشت به منوی خدمات">
      <i class="fas fa-chevron-right"></i>
    </a>
    <div class="form-header-title">
      <h1><i class="fas fa-stethoscope" style="color: #5A3DE0; margin-left: 6px;"></i> اطلاعات ویزیت</h1>
      <p>Appointments & Medical Visits</p>
    </div>
    <div style="width: 44px;"></div>
  </div>

  <!-- فرم ۱: استعلام ویزیت -->
  <div id="searchform" class="form-container">
    <h2><i class="fas fa-stethoscope icon"></i> جستجو و استعلام ویزیت</h2>

    <form method="POST">
      <label for="vid"><i class="fas fa-calendar-check icon"></i> شناسه ویزیت (کد عددی):</label>
      <input type="number" id="vid" name="vid" placeholder="شناسه عددی ویزیت را وارد کنید..." required>
      
      <button type="submit" name="search">
        <i class="fas fa-search icon"></i> جستجو در سامانه
      </button>

      <button type="button" onclick="showSecondForm()">
        <i class="fas fa-plus-circle icon"></i> ثبت ویزیت جدید
      </button>

      <?php if ($showButton): ?>
        <button type="button" onclick="showSecondForm()" class="btn-found-continue">
          <i class="fas fa-check-circle icon" style="color: #00B37E;"></i> ویزیت یافت شد؛ برای مشاهده و ویرایش کلیک کنید
        </button>
      <?php endif; ?>

      <button type="button" onclick="window.location.href='menu.html';">
        <i class="fas fa-arrow-right icon"></i> انصراف و بازگشت
      </button>
    </form>
  </div>

  <!-- فرم ۲: مشخصات کامل ویزیت -->
  <div id="visitform" class="form-container hidden">
    <h2><i class="fas fa-stethoscope icon"></i> مدیریت اطلاعات ویزیت</h2>

    <form method="POST">
      <label for="vid"><i class="fas fa-calendar-check icon"></i> شناسه ویزیت:</label>
      <input type="number" id="vid" name="vid" value="<?php echo isset($visit_search['VID']) ? $visit_search['VID'] : 0; ?>" required readonly>

      <label for="ptid"><i class="fas fa-hospital-user icon"></i> شناسه بیمار (کد ملی ۱۰ رقمی):</label>
      <input type="text" id="ptid" name="ptid" pattern="\d{10}" maxlength="10" placeholder="کد ملی باید ۱۰ رقم باشد" value="<?php echo isset($visit_search['PTID']) ? $visit_search['PTID'] : ''; ?>" required>

      <label for="drid"><i class="fas fa-user-md icon"></i> شناسه پزشک (کد ملی ۱۰ رقمی):</label>
      <input type="text" id="drid" name="drid" pattern="\d{10}" maxlength="10" placeholder="کد ملی باید ۱۰ رقم باشد" value="<?php echo isset($visit_search['DRID']) ? $visit_search['DRID'] : ''; ?>" required>

      <label for="date"><i class="fas fa-calendar-alt icon"></i> تاریخ ویزیت:</label>
      <input type="date" id="date" name="date" value="<?php echo isset($visit_search['DATE']) ? substr($visit_search['DATE'], 0, 10) : ''; ?>" required>

      <div class="button-group">  
        <button type="submit" name="save"><i class="fas fa-check icon"></i> ثبت</button>
        <button type="submit" name="update"><i class="fas fa-edit icon"></i> ویرایش</button>
        <button type="submit" name="delete"><i class="fas fa-trash-alt icon"></i> حذف</button>
        <button type="button" onclick="window.location.href='menu.html';"><i class="fas fa-times icon"></i> انصراف</button>
      </div>
    </form>
  </div>

  <script>
    function showSecondForm() {
      document.getElementById('searchform').classList.add("hidden");
      document.getElementById('visitform').classList.remove("hidden");
    }
  </script>
</body>
</html>
