<?php
class shop extends main
{
    function __construct()
    {
        parent::__construct();
    }
    // 打开页面
    function init()
    {
        $sid = $_GET['sid'];
        $db = new DB("shop");
        $shop = $db -> where("sid=$sid") -> select("*")[0];
        $this -> smarty -> assign("shop",$shop);
        $this -> smarty -> display("shop.html");
    }
    // 查询页面所有商品
    function detail()
    {
        $sid = $_GET["sid"];
        $res = [];
        $db = new DB("goodstype");
        $type = $db -> where("sid=$sid") -> select("*");
        $db = new DB("goods");
        $goods = $db -> where("sid=$sid") -> select("*");
        for ($i = 0; $i < count($type); $i++) {
            $res[$i]["title"] = $type[$i]["title"];
            $res[$i]["goods"] = [];
            for ($j = 0; $j < count($goods); $j++) {
                if ($goods[$j]["gid"] == $type[$i]["gid"]) {
                    array_push($res[$i]["goods"], $goods[$j]);
                }
            }
        }
        echo json_encode($res);
    }
    // 支付
    function car()
    {
        if (!$this -> islogin()) {
            echo json_encode(["code" => 1, "msg" => "请登录"]);
            exit();
        }
        $data = $_POST;
        unset($data["goods"]);
        $data["uid"] = $_SESSION["userid"];
        $db = new DB("orders");
        $rows = $db -> insert($data);
        if ($rows == 1) {
            $oid = $db -> mysql -> insert_id;
            $goods = $_POST["goods"];
            $keys = array_keys($goods[0]);
            array_push($keys,"oid");
            $db = new DB("orderextra");
            $sql = "insert into orderextra (";
            $str = implode(",",$keys);
            $sql .= $str . ") values ";
            $str = "";
            for ($i = 0; $i < count($goods); $i++) {
                $str .= "(";
                $goods[$i]["oid"] = $oid;
                foreach ($goods[$i] as $v) {
                    $str .= "'$v',";
                }
                $str = substr($str,0,-1) . "),";
            }
            $str = substr($str,0,-1);
            $sql .= $str;
            $rows = $db -> insert($sql);
            if ($rows >= 1) {
                echo json_encode(["code" => 0, "msg" => "下单成功"]);
            }
            else {
                echo json_encode(["code" => 2, "msg" => "订单详情失败"]);
            }
        }
        else {
            echo json_encode(["code" => 2, "msg" => "下单失败"]);
        }


    }
}