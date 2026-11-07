<?php
    $servername = "localhost";
    $username = "root";
    $password = ""; // رمزعبور دیتابیس شما
    $dbname = "health_check";
  
    $conn = new mysqli($servername, $username, $password, $dbname);
  
    // بررسی اتصال
    if ($conn->connect_error) {
        echo "<script>window.onload = function() { 
            Swal.fire({
                icon: 'error',
                title: 'اتصال به دیتابیس ناموفق بود',
                text: '" . $conn->connect_error . "',
                confirmButtonColor: '#d33'
            });
        }</script>";
        exit();
    }

    $showButton = false;
    
    if (isset($_POST['search'])) {
      $id = $_POST['id'];
      $visit_search = []; 
      
      $select_person = $conn->prepare("CALL select_person(?)");
      $select_person->bind_param("s", $id);
      $select_person->execute();
      $select_person_result = $select_person->get_result();
      
      if ($select_person_result-> num_rows > 0) {
        $person_search = $select_person_result->fetch_assoc();
        $showButton = true;
        }else{
          echo "<script>window.onload = function() { 
            Swal.fire({
                icon: 'error',
                title: 'شناسه وارد شده در بین افراد یافت نشد ... ابتدا فرد را ثبت کنید!',
                confirmButtonColor: '#28a745'
            });
        }</script>";
        }
      $select_person->close();
    }

    if (isset($_POST['save'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $gender = $_POST['gender'];
        $birth = $_POST['birth'];
        $tel = $_POST['tel'];
        $province = $_POST['province'];
        $city = $_POST['city'];
        $street = $_POST['street'];
        $plaque = $_POST['plaque'];

        // اجرای Stored Procedure
        $insert_person = $conn->prepare("CALL insert_person(?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_person->bind_param("ssssssssi", $id, $name, $gender, $birth, $tel, $province, $city, $street, $plaque);

        try {
            $insert_person->execute();
            echo "<script>window.onload = function() { 
                Swal.fire({
                    icon: 'success',
                    title: 'اطلاعات با موفقیت ذخیره شد!',
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

        $insert_person->close();
    }

    if (isset($_POST['update'])) {
      $id = $_POST['id'];
      $name = $_POST['name'];
      $gender = $_POST['gender'];
      $birth = $_POST['birth'];
      $tel = $_POST['tel'];
      $province = $_POST['province'];
      $city = $_POST['city'];
      $street = $_POST['street'];
      $plaque = $_POST['plaque'];

      // اجرای Stored Procedure
      $update_person = $conn->prepare("CALL update_person(?, ?, ?, ?, ?, ?, ?, ?, ?)");
      $update_person->bind_param("ssssssssi", $id, $name, $gender, $birth, $tel, $province, $city, $street, $plaque);

      try {
          $update_person->execute();
          echo "<script>window.onload = function() { 
              Swal.fire({
                  icon: 'success',
                  title: 'اطلاعات با موفقیت ویرایش شد!',
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

      $update_person->close();
  }

  if (isset($_POST['delete'])) {
    $id = $_POST['id'];
  
    $delete_person = $conn->prepare("CALL delete_person(?)");
    $delete_person->bind_param("s", $id);
  
      try {
        $delete_person->execute();
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
      $delete_person->close();
    
  }

    $conn->close();
    ?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ثبت اطلاعات افراد | سامانه سلامت</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Vazirmatn:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <!-- لکه‌های بنفش پس‌زمینه ارگانیک -->
  <div class="bg-blob-circle blob-c1"></div>
  <div class="bg-blob-circle blob-c2"></div>

  <!-- نوار بالای فرم -->
  <div class="form-top-bar">
    <a href="menu.html" class="nav-btn-back" title="بازگشت به منوی خدمات">
      <i class="fas fa-chevron-right"></i>
    </a>
    <div class="form-header-title">
      <h1><i class="fas fa-user-plus" style="color: #5A3DE0; margin-left: 6px;"></i> اطلاعات افراد</h1>
      <p>Emergency & Personal Information</p>
    </div>
    <div style="width: 44px;"></div>
  </div>

  <!-- فرم ۱: جستجوی مشخصات فرد -->
  <div id="searchform" class="form-container">
    <h2><i class="fas fa-search icon"></i> استعلام اطلاعات فرد</h2>

    <form method="POST">
      <label for="id"><i class="fas fa-id-card icon"></i> شناسه فرد (کد ملی ۱۰ رقمی):</label>
      <input type="text" id="id" name="id" pattern="\d{10}" maxlength="10" placeholder="کد ملی باید ۱۰ رقم باشد..." required>
      
      <button type="submit" name="search">
        <i class="fas fa-search icon"></i> جستجو در سامانه
      </button>

      <button type="button" onclick="showSecondForm()">
        <i class="fas fa-user-plus icon"></i> ثبت فرد جدید
      </button>

      <?php if ($showButton): ?>
        <button type="button" onclick="showSecondForm()" class="btn-found-continue">
          <i class="fas fa-check-circle icon" style="color: #00B37E;"></i> شناسه یافت شد؛ برای ادامه و ویرایش کلیک کنید
        </button>
      <?php endif; ?>

      <button type="button" onclick="window.location.href='menu.html';">
        <i class="fas fa-arrow-right icon"></i> انصراف و بازگشت
      </button>
    </form>
  </div>

  <!-- فرم ۲: مشخصات کامل فرد -->
  <div id="personform" class="form-container hidden">
    <h2><i class="fas fa-user-edit icon"></i> مدیریت پرونده فرد</h2>
    <form method="post">
      
      <label for="id"><i class="fas fa-id-card icon"></i> شناسه فرد (کد ملی):</label>
      <input type="text" id="id" name="id" pattern="\d{10}" maxlength="10" placeholder="کد ملی باید ۱۰ رقم باشد" value="<?php echo isset($person_search['ID']) ? $person_search['ID'] : ''; ?>" required>

      <label for="name"><i class="fas fa-user icon"></i> نام و نام خانوادگی:</label>
      <input type="text" id="name" name="name" placeholder="نام و نام خانوادگی..." value="<?php echo isset($person_search['name']) ? $person_search['name'] : ''; ?>" required>

      <label for="gender"><i class="fas fa-venus-mars icon"></i> جنسیت:</label>
      <select id="gender" name="gender" required>
        <option value="زن" <?php echo (isset($person_search['gender']) && $person_search['gender'] == 'زن') ? 'selected' : ''; ?>>زن</option>
        <option value="مرد" <?php echo (isset($person_search['gender']) && $person_search['gender'] == 'مرد') ? 'selected' : ''; ?>>مرد</option>
      </select>

      <label for="birth"><i class="fas fa-birthday-cake icon"></i> تاریخ تولد:</label>
      <input type="date" id="birth" name="birth" value="<?php echo isset($person_search['birth']) ? $person_search['birth'] : ''; ?>" required>

      <label for="tel"><i class="fas fa-phone icon"></i> شماره تماس:</label>
      <input type="tel" id="tel" name="tel" pattern="09[0-9]{9}" maxlength="11" placeholder="09xxxxxxxxx" value="<?php echo isset($person_search['tel']) ? $person_search['tel'] : ''; ?>" required>

      <fieldset>
        <legend><i class="fas fa-map-marker-alt icon"></i> آدرس محل سکونت</legend>
        
        <label for="province">استان:</label>
        <input type="text" id="province" name="province" placeholder="استان..." value="<?php echo isset($person_search['province']) ? $person_search['province'] : ''; ?>" required>

        <label for="city">شهر:</label>
        <input type="text" id="city" name="city" placeholder="شهر..." value="<?php echo isset($person_search['city']) ? $person_search['city'] : ''; ?>" required>

        <label for="street">خیابان:</label>
        <input type="text" id="street" name="street" placeholder="خیابان..." value="<?php echo isset($person_search['street']) ? $person_search['street'] : ''; ?>" required>

        <label for="plaque">پلاک:</label>
        <input type="number" id="plaque" name="plaque" placeholder="شماره پلاک..." value="<?php echo isset($person_search['plaque']) ? $person_search['plaque'] : ''; ?>" required>
      </fieldset>

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
      document.getElementById('personform').classList.remove("hidden");
    }
  </script>
</body>
</html>
