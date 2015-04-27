<?php
defined('HB_FL_YG') || define('HB_FL_YG', 1);     //员工
defined('HB_FL_KH') || define('HB_FL_KH', 2);     //客户
defined('HB_FL_GYS') || define('HB_FL_GYS', 3);     //供应商
defined('HB_FL_JRXGR') || define('HB_FL_JRXGR', 4);     //金融相关人

defined('HT_FL_LD') || define('HT_FL_LD', 1); //劳动合同
defined('HT_FL_CG') || define('HT_FL_CG', 2); // 采购合同
defined('HT_FL_XS') || define('HT_FL_XS', 3); //销售合同

defined('HT_XZ_CHANGQI') || define('HT_XZ_CHANGQI', 1); //长期合同
defined('HT_XZ_ZHOUQI') || define('HT_XZ_ZHOUQI', 2);	// 周期性合同
defined('HT_XZ_BUCHONG') || define('HT_XZ_BUCHONG', 3); // 补充合同

defined('DINGDAN_STATUS_ZHIXING') || define('DINGDAN_STATUS_ZHIXING', 1); //订单执行中
defined('DINGDAN_STATUS_JIESHU') || define('DINGDAN_STATUS_JIESHU', 2); //订单已完成
defined('DINGDAN_STATUS_QUXIAO') || define('DINGDAN_STATUS_QUXIAO', 3); //订单已取消

defined('YW_FL_XD') || define('YW_FL_XD', 1); //下采购单
defined('YW_FL_SH') || define('YW_FL_SH', 2); //收货
defined('YW_FL_SC') || define('YW_FL_SC', 3); //生产
defined('YW_FL_ZJBD') || define('YW_FL_ZJBD', 4); //资金变动
defined('YW_FL_JD') || define('YW_FL_JD', 5); //接客户订单
defined('YW_FL_FH') || define('YW_FL_FH', 6);//发货
defined('YW_FL_PDCK') || define('YW_FL_PDCK', 6); //盘点仓库
defined('YW_FL_PDZJ') || define('YW_FL_PDZJ', 8); //盘点资金
defined('YW_FL_WZYK') || define('YW_FL_WZYK', 8); //物资移库

defined('WZ_PACKAGE_FL_DINGDAN') || define('WZ_PACKAGE_FL_DINGDAN', 1); //订单包
defined('WZ_PACKAGE_FL_YUNSHU') || define('WZ_PACKAGE_FL_YUNSHU', 1); //运输包
defined('WZ_PACKAGE_FL_KUGUAN') || define('WZ_PACKAGE_FL_KUGUAN', 1); //库管包


defined('WZ_YUNSHU') || define('WZ_YUNSHU', 1);   //运输
defined('WZ_ZHUANGXIE') || define('WZ_ZHUANGXIE', 2); //装卸
    
defined('WZ_DL_YUANLIAO') || define('WZ_DL_YUANLIAO', 1); //原料
defined('WZ_DL_SHEBEI') || define('WZ_DL_SHEBEI', 2);     //设备
defined('WZ_DL_CHANPIN') || define('WZ_DL_CHANPIN', 3);     //产品
    
defined('ZJ_CAUSE_CG') || define('ZJ_CAUSE_CG', 1);     //支付采购款
defined('ZJ_CAUSE_YUNSHU') || define('ZJ_CAUSE_YUNSHU', 2);     //支付运输费
defined('ZJ_CAUSE_ZHUANGXIE') || define('ZJ_CAUSE_ZHUANGXIE', 3);     //支付装卸费

defined('ZJ_FL_XIANJIN') || define('ZJ_FL_XIANJIN', 1);     //现金
defined('ZJ_FL_XIANJINZHIPIAO') || define('ZJ_FL_XIANJINZHIPIAO', 2);     //现金支票
defined('ZJ_FL_CHENGDUIHUIPIAO') || define('ZJ_FL_CHENGDUIHUIPIAO', 3);     //银行承兑

defined('GX_FL_ZH') || define('GX_FL_ZH', 1);     //工序类型：置换
defined('GX_FL_ZUHE') || define('GX_FL_ZUHE', 2);     //工序类型：组合
defined('GX_FL_FJ') || define('GX_FL_FJ', 3);     //工序类型：分解
defined('GX_FL_TG') || define('GX_FL_TG', 4);     //工序类型：涂裹
defined('GX_FL_JG') || define('GX_FL_JG', 5);     //工序类型：加工
defined('GX_FL_FSCX') || define('GX_FL_FSCX', 6);     //工序类型：非生产性工序

defined('GX_CG') || define('GX_CG', 1);     //工序：采购
defined('GX_ZX') || define('GX_ZX', 2);     //工序：造型
defined('GX_LXQL') || define('GX_LXQL', 3);     //工序蜡型清理
defined('GX_LXHJ') || define('GX_LXHJ', 4);     //工序：蜡型焊接
defined('GX_TLZM') || define('GX_TLZM', 5);     //工序：涂料制模
defined('GX_SL') || define('GX_SL', 6);     //工序：失蜡
defined('GX_JZ') || define('GX_JZ', 7);     //工序：浇注
defined('GX_QS') || define('GX_QS', 8);     //工序：清砂
defined('GX_CPFJ') || define('GX_CPFJ', 9);     //工序：产品分解
defined('GX_YJFJ') || define('GX_YJFJ', 10);     //工序：一极分拣
defined('GX_PWQS') || define('GX_PWQS', 11);     //工序：抛丸清砂
defined('GX_HJXB') || define('GX_HJXB', 12);     //工序：焊接修补
defined('GX_DM') || define('GX_DM', 13);     //工序：打磨
defined('GX_EJFJ') || define('GX_EJFJ', 14);     //工序：二级分拣
defined('GX_BZ') || define('GX_BZ', 15);     //工序：包装
defined('GX_JYRK') || define('GX_JYRK', 16);     //工序：检验入库

defined('GX_LAST') || define('GX_LAST', GX_JYRK);     //工序：最后一道工序


defined('WZ_FL_YUANLIAO') || define('WZ_FL_YUANLIAO', 1);     //原料
defined('WZ_FL_SHEBEI') || define('WZ_FL_SHEBEI', 2);     //设备
defined('WZ_FL_CHANPIN') || define('WZ_FL_CHANPIN', 3);     //产品
defined('WZ_FL_FUWU') || define('WZ_FL_FUWU', 4);     //服务
defined('WZ_FL_LAOBAO') || define('WZ_FL_LAOBAO', 5);     //劳保用品
defined('WZ_FL_BANGONG') || define('WZ_FL_BANGONG', 6);     //办公用品
defined('WZ_FL_WEIXIU') || define('WZ_FL_WEIXIU', 7);     //维修用品
defined('WZ_FL_NENGYUAN') || define('WZ_FL_NENGYUAN', 8);     //能源
defined('WZ_FL_QITA') || define('WZ_FL_QITA', 9);     //其他

defined('CALC_METHOD_GUDING') || define('CALC_METHOD_GUDING', 1);     //固定值
defined('CALC_METHOD_TJBL') || define('CALC_METHOD_TJBL', 2);     //体积比例
defined('CALC_METHOD_BMJBL') || define('CALC_METHOD_BMJBL', 3);     //表面积比例

?>