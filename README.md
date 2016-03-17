# API for image resize
This is simple image resize api. All responses in JSON format.
Required: PHP and GD module

# Examples
Init new user key
----------------------------
```bash
GET img_api.php?action=init
```

Example Result

```bash
{"success":true,"key":"f096565d34554f66963125f37fb899d9eb430b8f"}
```

Get all images by key
----------------------------
```bash
POST img_api.php?action=get
```

Parameters

```bash
key - user unique key. Required. Example Values: f096565d34554f66963125f37fb899d9eb430b8f
```

Example Result

```bash
[{"img_url":"userdata\/upload\/77346110956ea8dd88aa1f.png","width":100,"height":100}]
```

Upload and resize new image
----------------------------
```bash
POST img_api.php?action=upload
```

Parameters

```bash
key - user unique key. Required. Example Values: f096565d34554f66963125f37fb899d9eb430b8f
image - user`s image. Required. Type: file. Available only .jpg, .png, .gif
width - new width parameter. Optional. Default: original image width. Example Values: 100
height - new height parameter. Optional. Default: original image height. Example Values: 100
```

Example Result

```bash
[{"img_url":"userdata\/upload\/77346110956ea8dd88aa1f.png","width":100,"height":100}]
```
