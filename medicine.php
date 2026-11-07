<?php
$servername = "localhost";
$username = "root";
$password = "15483";
$dbname = "health_check";

$conn = mysqli_connect($servername, $username, $password, $dbname);

$showButton = false;

if (isset($_POST['search'])) {
  $mid = $_POST['mid'];
  $medicine_search = [];

  $select_medicine = $conn->prepare("CALL select_medicine(?)");
  $select_medicine->bind_param("i", $mid);
  $select_medicine->execute();
  $select_medicine_result = $select_medicine->get_result();
  
  if ($select_medicine_result-> num_rows > 0) {
    $medicine_search = $select_medicine_result->fetch_assoc();
    $showButton = true;
    }else{
      echo "<script>window.onload = function() { 
        Swal.fire({
            icon: 'error',
            title: 'شناسه وارد شده در بین دارو ها یافت نشد ... ابتدا دارو را ثبت کنید!',
            confirmButtonColor: '#28a745'
        });
    }</script>";
    }
  $select_medicine->close();
}
  // ثبت یا ویرایش بیمار
    if (isset($_POST['save'])){
    $mid = $_POST['mid'];
    $vid = $_POST['vid'];
    $medicine = $_POST['medicine'];

    if($mid == 0){
    $insert_medicine = $conn->prepare("CALL insert_medicine(?, ?)");
    $insert_medicine->bind_param("is", $vid, $medicine);
    
    try {
      $insert_medicine->execute();
      echo "<script>window.onload = function() { 
          Swal.fire({
              icon: 'success',
              title: 'دارو با موفقیت ثبت شد!',
              confirmButtonColor: '#28a745'
          });
      }</script>";
  } catch (mysqli_sql_exception $e) {
    // وقتی خطا از SQL می‌آید، آن را مدیریت می‌کنیم
    $error_message = $e->getMessage();
    echo "<script>window.onload = function() { 
        Swal.fire({
            icon: 'error',
            title: 'خطا',
            text: '" . $error_message . "',
            confirmButtonColor: '#d33'
        });
    }</script>";
}
  $insert_medicine->close();
    }else{
      echo "<script>window.onload = function() { 
        Swal.fire({
            icon: 'error',
            title: 'خطا',
            text: 'دارو قبلا ثبت شده است!',
            confirmButtonColor: '#d33'
        });
    }</script>";
    
}
        }

  if (isset($_POST['update'])) {
    $mid = $_POST['mid'];
    $vid = $_POST['vid'];
    $medicine = $_POST['medicine'];

    if($mid == 0){
      echo "<script>window.onload = function() { 
        Swal.fire({
            icon: 'error',
            title: 'خطا',
            text: 'دارو قبلا ثبت نشده است!',
            confirmButtonColor: '#d33'
        });
    }</script>";
    
    }else{
      $update_medicine = $conn->prepare("CALL update_medicine(?, ?, ?)");
      $update_medicine->bind_param("iis", $mid, $vid, $medicine);
    
    try {
      $update_medicine->execute();
      echo "<script>window.onload = function() { 
          Swal.fire({
              icon: 'success',
              title: 'دارو با موفقیت ویرایش شد!',
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
  $update_medicine->close();
}

}
// حذف بیمار
if (isset($_POST['delete'])) {
  $mid = $_POST['mid'];

  $delete_medicine = $conn->prepare("CALL delete_medicine(?)");
  $delete_medicine->bind_param("i", $mid);

    try {
      $delete_medicine->execute();
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
    $delete_medicine->close();
  
}

$conn->close();

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ثبت اطلاعات داروها | سامانه سلامت</title>
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
      <h1><i class="fas fa-capsules" style="color: #5A3DE0; margin-left: 6px;"></i> اطلاعات داروها</h1>
      <p>Medicines & Prescriptions</p>
    </div>
    <div style="width: 44px;"></div>
  </div>

  <!-- فرم ۱: استعلام دارو -->
  <div id="searchform" class="form-container">
    <h2><i class="fas fa-capsules icon"></i> جستجو و استعلام دارو</h2>

    <form method="POST">
      <label for="mid"><i class="fas fa-id-card icon"></i> شناسه دارو (کد عددی):</label>
      <input type="number" id="mid" name="mid" placeholder="شناسه عددی دارو را وارد کنید..." required>
      
      <button type="submit" name="search">
        <i class="fas fa-search icon"></i> جستجو در سامانه
      </button>

      <button type="button" onclick="showSecondForm()">
        <i class="fas fa-plus-circle icon"></i> ثبت داروی جدید
      </button>

      <?php if ($showButton): ?>
        <button type="button" onclick="showSecondForm()" class="btn-found-continue">
          <i class="fas fa-check-circle icon" style="color: #00B37E;"></i> دارو یافت شد؛ برای مشاهده و ویرایش کلیک کنید
        </button>
      <?php endif; ?>

      <button type="button" onclick="window.location.href='menu.html';">
        <i class="fas fa-arrow-right icon"></i> انصراف و بازگشت
      </button>
    </form>
  </div>

  <!-- فرم ۲: مشخصات کامل دارو -->
  <div id="medicineform" class="form-container hidden">
    <h2><i class="fas fa-capsules icon"></i> مدیریت اطلاعات دارو</h2>

    <form method="POST">
      <label for="mid"><i class="fas fa-id-card icon"></i> شناسه دارو:</label>
      <input type="number" id="mid" name="mid" value="<?php echo isset($medicine_search['MID']) ? $medicine_search['MID'] : 0 ; ?>" required readonly>

      <label for="vid"><i class="fas fa-calendar-check icon"></i> شناسه ویزیت مربوطه:</label>
      <input type="number" id="vid" name="vid" placeholder="شناسه ویزیت..." value="<?php echo isset($medicine_search['VID']) ? $medicine_search['VID'] : 0 ; ?>" required>

      <label for="medicine"><i class="fas fa-pills icon"></i> نام و مشخصات داروها:</label>
      <input type="text" id="medicine" name="medicine" placeholder="شرح دارو، دوز و نحوه مصرف..." value="<?php echo isset($medicine_search['medicine']) ? $medicine_search['medicine'] : ' '; ?>" required>

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
      document.getElementById('medicineform').classList.remove("hidden");
    }
  </script>
</body>
</html>
