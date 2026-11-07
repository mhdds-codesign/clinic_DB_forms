<?php
$servername = "localhost";
$username = "root";
$password = "15483";
$dbname = "health_check";

$conn = mysqli_connect($servername, $username, $password, $dbname);
 
$showButton = false; 

if (isset($_POST['search'])) {
  $hsid = $_POST['hsid'];
  $statue_search = []; 

  $select_statue = $conn->prepare("CALL select_statue(?)");
  $select_statue->bind_param("i", $hsid);
  $select_statue->execute();
  $select_statue_result = $select_statue->get_result();
  
  if ($select_statue_result-> num_rows > 0) {
    $statue_search = $select_statue_result->fetch_assoc();
    $showButton = true;
    }else{
      echo "<script>window.onload = function() { 
        Swal.fire({
            icon: 'error',
            title: 'شناسه وارد شده در بین علائم یافت نشد ... ابتدا علائم را ثبت کنید!',
            confirmButtonColor: '#28a745'
        });
    }</script>";
    }
  $select_statue->close();
}
  // ثبت یا ویرایش بیمار
    if (isset($_POST['save'])){
    $hsid = $_POST['hsid'];
    $sid = $_POST['sid'];
    $date = $_POST['date'];
    $symptoms = $_POST['symptoms'];

    if($hsid == 0){
      $insert_statue = $conn->prepare("CALL insert_statue(?, ?, ?)");
    $insert_statue->bind_param("sss", $sid, $date, $symptoms);
    
    try {
      $insert_statue->execute();
      echo "<script>window.onload = function() { 
          Swal.fire({
              icon: 'succ ness',
              title: 'علائم با موفقیت ثبت شد!',
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
  $insert_statue->close();
    }else{
      echo "<script>window.onload = function() { 
        Swal.fire({
            icon: 'error',
            title: 'خطا',
            text: 'علائم قبلا ثبت شده است!',
            confirmButtonColor: '#d33'
        });
    }</script>";
    
}

        }

  if (isset($_POST['update'])) {
    $hsid = $_POST['hsid'];
    $sid = $_POST['sid'];
    $date = $_POST['date'];
    $symptoms = $_POST['symptoms'];

    if($hsid == 0){
      echo "<script>window.onload = function() { 
        Swal.fire({
            icon: 'error',
            title: 'خطا',
            text: 'علائم قبلا ثبت نشده است!',
            confirmButtonColor: '#d33'
        });
    }</script>";
    
    }else{
    $update_statue = $conn->prepare("CALL update_statue(?, ?, ? ,?)");
    $update_statue->bind_param("isss", $hsid, $sid, $date, $symptoms);
    
    try {
      $update_statue->execute();
      echo "<script>window.onload = function() { 
          Swal.fire({
              icon: 'success',
              title: 'علائم با موفقیت ویرایش شد!',
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
  $update_statue->close();
}

}
// حذف بیمار
if (isset($_POST['delete'])) {
  $hsid = $_POST['hsid'];

  $delete_statue = $conn->prepare("CALL delete_statue(?)");
  $delete_statue->bind_param("i", $hsid);

    try {
      $delete_statue->execute();
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
    $delete_statue->close();
  
}


$conn->close();

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ثبت اطلاعات علائم | سامانه سلامت</title>
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
      <h1><i class="fas fa-clipboard-check" style="color: #5A3DE0; margin-left: 6px;"></i> ثبت علائم بیمار</h1>
      <p>Patient Symptoms & Checkup</p>
    </div>
    <div style="width: 44px;"></div>
  </div>

  <!-- فرم ۱: استعلام علائم -->
  <div id="searchform" class="form-container">
    <h2><i class="fas fa-clipboard-check icon"></i> جستجو و استعلام علائم</h2>

    <form method="POST">
      <label for="hsid"><i class="fas fa-calendar-check icon"></i> شناسه ثبت علائم (کد عددی):</label>
      <input type="number" id="hsid" name="hsid" placeholder="شناسه عددی علائم را وارد کنید..." required>
      
      <button type="submit" name="search">
        <i class="fas fa-search icon"></i> جستجو در سامانه
      </button>

      <button type="button" onclick="showSecondForm()">
        <i class="fas fa-plus-circle icon"></i> ثبت علائم جدید
      </button>

      <?php if ($showButton): ?>
        <button type="button" onclick="showSecondForm()" class="btn-found-continue">
          <i class="fas fa-check-circle icon" style="color: #00B37E;"></i> علائم یافت شد؛ برای مشاهده و ویرایش کلیک کنید
        </button>
      <?php endif; ?>

      <button type="button" onclick="window.location.href='menu.html';">
        <i class="fas fa-arrow-right icon"></i> انصراف و بازگشت
      </button>
    </form>
  </div>

  <!-- فرم ۲: مشخصات کامل علائم -->
  <div id="statueform" class="form-container hidden">
    <h2><i class="fas fa-head-side-virus icon"></i> مدیریت اطلاعات علائم بیمار</h2>

    <form method="POST">
      <label for="hsid"><i class="fas fa-id-card icon"></i> شناسه علائم:</label>
      <input type="number" id="hsid" name="hsid" value="<?php echo isset($statue_search['HSID']) ? $statue_search['HSID'] : 0; ?>" required readonly>

      <label for="sid"><i class="fas fa-hospital-user icon"></i> شناسه بیمار (کد ملی ۱۰ رقمی):</label>
      <input type="text" id="sid" name="sid" pattern="\d{10}" maxlength="10" placeholder="کد ملی باید ۱۰ رقم باشد" value="<?php echo isset($statue_search['SID']) ? $statue_search['SID'] : ''; ?>" required>

      <label for="date"><i class="fas fa-calendar-alt icon"></i> تاریخ ثبت علائم:</label>
      <input type="date" id="date" name="date" value="<?php echo isset($statue_search['date']) ? substr($statue_search['date'], 0, 10) : ''; ?>" required>

      <label for="symptoms"><i class="fas fa-head-side-virus icon"></i> شرح علائم:</label>
      <input type="text" id="symptoms" name="symptoms" placeholder="شرح علائم مشاهده شده..." value="<?php echo isset($statue_search['symptoms']) ? $statue_search['symptoms'] : ''; ?>" required>

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
      document.getElementById('statueform').classList.remove("hidden");
    }
  </script>
</body>
</html>
