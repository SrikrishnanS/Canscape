<?php
if(!isset($_FILES["image"]))
{
	echo "__Big Error__";
}
else
{
	if (($_FILES["image"]["type"] == "image/gif")
	|| ($_FILES["image"]["type"] == "image/jpeg")
	|| ($_FILES["image"]["type"] == "image/png")
	|| ($_FILES["image"]["type"] == "image/svg")
	|| ($_FILES["image"]["type"] == "image/svg+xml"))
	{
	  if ($_FILES["image"]["error"] > 0)
	  {
			echo "__Error__";
	  }
	  else
	  {
			 move_uploaded_file($_FILES["image"]["tmp_name"],"pImages/" . $_FILES["image"]["name"]);
			 $filename="pImages/". $_FILES["image"]["name"];
			 $imgsize = getimagesize($filename);
			 echo $filename.",".$imgsize[0].",".$imgsize[1];
	  }
	}
	
	else
	{
	  echo "__Invalid file__";
	}
}
?>
