<?php
$servername = getenv('DB_HOST') ?: 'mysql-clinic-db-mhdds-e2c8.h.aivencloud.com';
$username = getenv('DB_USER') ?: 'avnadmin';
$password = getenv('DB_PASS') ?: (file_exists(__DIR__ . '/secret_key.php') ? include(__DIR__ . '/secret_key.php') : '');
$dbname = getenv('DB_NAME') ?: 'defaultdb';
$port = 28675;

$conn = mysqli_connect($servername, $username, $password, $dbname, $port);

$showButton = false;

if (isset($_POST['search'])) {
  $pid = $_POST['pid'];
  $person_search = [];
  $patient_search = [];

  $select_person = $conn->prepare("CALL select_personforpat(?)");
  $select_person->bind_param("s", $pid);
  $select_person->execute();
  $select_person_result = $select_person->get_result();

  if ($select_person_result-> num_rows > 0) {
    $person_search = $select_person_result->fetch_assoc();
    $showButton = true;

    }else{
      echo "<script>window.onload = function() { 
        Swal.fire({
            icon: 'error',
            title: 'شناسه وارد شده در بین افراد  یافت نشد ... ابتدا فرد را ثبت کنید!',
            confirmButtonColor: '#28a745'
        });
    }</script>";
    }
    $select_person->close();

  $select_patient = $conn->prepare("CALL select_patient(?)");
  $select_patient->bind_param("s", $pid);
  $select_patient->execute();
  $select_patient_result = $select_patient->get_result();
  
  if ($select_patient_result-> num_rows > 0) {
    $patient_search = $select_patient_result->fetch_assoc();
    }
  $select_patient->close();
  
}
  // ثبت یا ویرایش بیمار
  if (isset($_POST['save'])){
    $pid = $_POST['pid'];
    $name = $_POST['name'];
    $gender = $_POST['gender'];
    $birth = $_POST['birth'];
    $tel = $_POST['tel'];
    $province = $_POST['province'];
    $city = $_POST['city'];
    $street = $_POST['street'];
    $plaque = $_POST['plaque'];
    $age = $_POST['age'];
    $sickhistory = $_POST['sickhistory'];

    $insert_update_patient = $conn->prepare("CALL insert_update_patient(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insert_update_patient->bind_param("ssssssssiis", $pid, $name, $gender, $birth, $tel, $province, $city, $street ,$plaque, $age, $sickhistory);
    $insert_update_patient->execute();
    
    if ($insert_update_patient->errno === 0) {
        echo "<script>window.onload = function() { 
            Swal.fire({
                icon: 'success',
                title: 'اطلاعات با موفقیت ثبت یاویرایش شدند!',
                confirmButtonColor: '#28a745'
            });
        }</script>";
  } else {
    echo "<script>window.onload = function() { 
        Swal.fire({
            icon: 'error',
            title: ' ثبت یا ویرایش اطلاعات با خطا مواجه شده است ...مجدد تلاش نمایید!',
            confirmButtonColor: '#d33'
        });
    }</script>";
}

  $insert_update_patient->close();
    
}

// حذف بیمار
if (isset($_POST['delete'])) {
    $pid = $_POST['pid'];
    

    $delete_patient = $conn->prepare("CALL delete_patient(?)");
    $delete_patient->bind_param("s", $pid);

    try {
      $delete_patient->execute();
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
    $delete_patient->close();
    
}

$conn->close();

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ثبت اطلاعات بیمار | سامانه سلامت</title>
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
      <h1><i class="fas fa-hospital-user" style="color: #5A3DE0; margin-left: 6px;"></i> اطلاعات بیمار</h1>
      <p>Patient Record & Health Care</p>
    </div>
    <div style="width: 44px;"></div>
  </div>

  <!-- فرم ۱: استعلام بیمار -->
  <div id="searchform" class="form-container">
    <h2><i class="fas fa-hospital-user icon"></i> جستجو و استعلام بیمار</h2>

    <form method="POST">
      <label for="pid"><i class="fas fa-id-card icon"></i> شناسه بیمار (کد ملی ۱۰ رقمی):</label>
      <input type="text" id="pid" name="pid" pattern="\d{10}" maxlength="10" placeholder="کد ملی باید ۱۰ رقم باشد..." required>
      
      <button type="submit" name="search">
        <i class="fas fa-search icon"></i> جستجو در سامانه
      </button>

      <?php if ($showButton): ?>
        <button type="button" onclick="showSecondForm()" class="btn-found-continue">
          <i class="fas fa-check-circle icon" style="color: #00B37E;"></i> شناسه یافت شد؛ برای ادامه و ثبت اطلاعات کلیک کنید
        </button>
      <?php endif; ?>

      <button type="button" onclick="window.location.href='menu.html';">
        <i class="fas fa-arrow-right icon"></i> انصراف و بازگشت
      </button>
    </form>
  </div>

  <!-- فرم ۲: مشخصات کامل بیمار -->
  <div id="patientform" class="form-container hidden">
    <h2><i class="fas fa-hospital-user icon"></i> مدیریت پرونده بیمار</h2>

    <form method="POST">
      <label for="pid"><i class="fas fa-id-card icon"></i> شناسه بیمار:</label>
      <input type="text" id="pid" name="pid" pattern="\d{10}" maxlength="10" placeholder="کد ملی باید ۱۰ رقم باشد" value="<?php echo isset($person_search['ID']) ? $person_search['ID'] : ''; ?>" required readonly>

      <label for="name"><i class="fas fa-user icon"></i> نام و نام خانوادگی:</label>
      <input type="text" id="name" name="name" value="<?php echo isset($person_search['name']) ? $person_search['name'] : ''; ?>" required readonly>

      <label for="gender"><i class="fas fa-venus-mars icon"></i> جنسیت:</label>
      <select id="gender" name="gender" required readonly>
        <option value="زن" <?php echo (isset($person_search['gender']) && $person_search['gender'] == 'زن') ? 'selected' : ''; ?>>زن</option>
        <option value="مرد" <?php echo (isset($person_search['gender']) && $person_search['gender'] == 'مرد') ? 'selected' : ''; ?>>مرد</option>
      </select>

      <label for="birth"><i class="fas fa-birthday-cake icon"></i> تاریخ تولد:</label>
      <input type="date" id="birth" name="birth" value="<?php echo isset($person_search['birth']) ? $person_search['birth'] : ''; ?>" required readonly>
      
      <label for="tel"><i class="fas fa-phone icon"></i> شماره تماس:</label>
      <input type="tel" id="tel" name="tel" pattern="09[0-9]{9}" maxlength="11" placeholder="09xxxxxxxxx" value="<?php echo isset($person_search['tel']) ? $person_search['tel'] : ''; ?>" required readonly>

      <fieldset>
        <legend><i class="fas fa-map-marker-alt icon"></i> آدرس</legend>
        
        <label for="province">استان:</label>
        <input type="text" id="province" name="province" value="<?php echo isset($person_search['province']) ? $person_search['province'] : ''; ?>" required readonly>

        <label for="city">شهر:</label>
        <input type="text" id="city" name="city" value="<?php echo isset($person_search['city']) ? $person_search['city'] : ''; ?>" required readonly>

        <label for="street">خیابان:</label>
        <input type="text" id="street" name="street" value="<?php echo isset($person_search['street']) ? $person_search['street'] : ''; ?>" required readonly>

        <label for="plaque">پلاک:</label>
        <input type="number" id="plaque" name="plaque" value="<?php echo isset($person_search['plaque']) ? $person_search['plaque'] : ''; ?>" required readonly>
      </fieldset>

      <label for="age"><i class="fas fa-birthday-cake icon"></i> سن بیمار:</label>
      <input type="number" id="age" name="age" placeholder="سن به سال..." value="<?php echo isset($patient_search['age']) ? $patient_search['age'] : ''; ?>" required>

      <label for="sickhistory"><i class="fas fa-briefcase-medical icon"></i> سابقه بیماری:</label>
      <input type="text" id="sickhistory" name="sickhistory" placeholder="شرح سابقه بیماری‌های قبلی..." value="<?php echo isset($patient_search['sickhistory']) ? $patient_search['sickhistory'] : ''; ?>" required>

      <div class="button-group">
        <button type="submit" name="save"><i class="fas fa-check icon"></i> ثبت</button>
        <button type="button" onclick="enableEdit()"><i class="fas fa-edit icon"></i> ویرایش</button>
        <button type="submit" name="delete"><i class="fas fa-trash-alt icon"></i> حذف</button>
        <button type="button" onclick="window.location.href='menu.html';"><i class="fas fa-times icon"></i> انصراف</button>
      </div>
    </form>
  </div>

  <script>
    function showSecondForm() {
      document.getElementById('searchform').classList.add("hidden");
      document.getElementById('patientform').classList.remove("hidden");
    }

    function enableEdit() {
      const fields = ['name', 'birth', 'gender', 'tel', 'province', 'city', 'street', 'plaque'];
      fields.forEach(field => document.getElementById(field).removeAttribute('readonly'));
    }
  </script>
</body>
</html>
