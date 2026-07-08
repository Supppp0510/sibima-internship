<?php

include "../../config/config.php";

$bidang = $_GET['bidang_id'];

$selected = isset($_GET['selected'])
    ? $_GET['selected']
    : "";

$query = mysqli_query($conn,"
SELECT *
FROM pembimbing
WHERE bidang_id='$bidang'
ORDER BY nama ASC
");

echo "<option value=''>-- Pilih Pembimbing --</option>";

while($row=mysqli_fetch_assoc($query)){

    $pilih = "";

    if($selected==$row['id']){

        $pilih="selected";

    }

    echo "

    <option

    value='".$row['id']."'

    ".$pilih.">

    ".$row['nama']."

    </option>

    ";

}

?>