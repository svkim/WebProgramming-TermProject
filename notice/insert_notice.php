<? @session_start() ?>
<meta charset = "utf-8">



<?
$regist_day = date("Y-m-d (H:i)"); // 현재의 년-월-일-시-분'을 저장'

// upfile[]에 담겨있음

// 다중 파일 업로드
$files = $_FILES["upfile"];
$count = count($files["name"]);

$upload_dir = '../data/';

for($i = 0; $i < $count; $i++)
{
    $upfile_name[$i] = $files["name"][$i];
    $upfile_tmp_name[$i] = $files["tmp_name"][$i];
    $upfile_type[$i] = $files["type"][$i];
    $upfile_size[$i] = $files["size"][$i];
    $upfile_error[$i] = $files["error"][$i];

    $file = explode(".", $upfile_name[$i]);
    $file_name = $file[0];
    $file_ext = $file[1];
    
    if (!$upfile_error[$i])
    {
        $new_file_name = date("Y_m_d_H_i_s");
        $new_file_name = $new_file_name."_".$i;
        $copied_file_name[$i] = $new_file_name.".".$file_ext;      
        $uploaded_file[$i] = $upload_dir.$copied_file_name[$i];


        if( !move_uploaded_file($upfile_tmp_name[$i], $uploaded_file[$i]) )
        {
            echo("
            <script>
            alert('파일을 지정한 디렉토리에 복사하는데 실패했습니다.');
            history.go(-1)
            </script>    
            ");
            exit;
        }
    }
}

include "../lib/dbconn_admin.php";



    //수정하기라면

	if ($mode=="modify")
	{
		$num_checked = count($_POST['del_file']);
		$position = $_POST['del_file'];

		for($i=0; $i<$num_checked; $i++)                      // delete checked item
		{
			$index = $position[$i];
			$del_ok[$index] = "y";
		}

		$sql = "select * from $table where num=$num";   // get target record
		$result = mysql_query($sql);
		$row = mysql_fetch_array($result);

		for ($i=0; $i<$count; $i++)					// update DB with the value of file input box
		{

			$field_org_name = "file_name_".$i;
			$field_real_name = "file_copied_".$i;

			$org_name_value = $upfile_name[$i];
			$org_real_value = $copied_file_name[$i];
			if ($del_ok[$i] == "y")
			{
				$delete_field = "file_copied_".$i;
				$delete_name = $row[$delete_field];
				
				$delete_path = "../data/".$delete_name;

				unlink($delete_path);

				$sql = "update $table set $field_org_name = '$org_name_value', $field_real_name = '$org_real_value'  where num=$num";
				mysql_query($sql, $connect);  // $sql 에 저장된 명령 실행
			}
			else
			{
				if (!$upfile_error[$i])
				{
					$sql = "update $table set $field_org_name = '$org_name_value', $field_real_name = '$org_real_value'  where num=$num";
					mysql_query($sql, $connect);  // $sql 에 저장된 명령 실행					
				}
			}

		}
		$sql = "update $table set subject='$ntsubj', content='$nttext' where num=$num";
		mysql_query($sql, $connect);  // $sql 에 저장된 명령 실행
	}
	else
	{
        // html 안씀
		// if ($html_ok=="y")
		// {
		// 	$is_html = "y";
		// }
		// else
		// {
		// 	$is_html = "";
		// 	$nttext = htmlspecialchars($nttext);
		// }

		$sql = "insert into $table (id, name, subject, content, regist_day, hit, ";
		$sql .= " file_name_0, file_name_1, file_name_2, file_copied_0,  file_copied_1, file_copied_2) ";
		$sql .= "values('$s_id', '$s_name', '$ntsubj', '$nttext', '$regist_day', 0, ";
		$sql .= "'$upfile_name[0]', '$upfile_name[1]',  '$upfile_name[2]', '$copied_file_name[0]', '$copied_file_name[1]','$copied_file_name[2]')";
		mysql_query($sql, $connect);  // $sql 에 저장된 명령 실행
	}

	mysql_close();                // DB 연결 끊기

	echo "
	   <script>
	    location.href = '../notice.php?table=$table&page=$page';
	   </script>
	";
?>