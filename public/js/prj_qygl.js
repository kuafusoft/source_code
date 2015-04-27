var XT;
XT = XT || {};
(function(){
	var tool = new kf_tool();
	this.yg_index = function(){
		return this.grid_index('qygl', 'zzvw_yg', '员工管理');
	}
	
	this.gys_index = function(){
		return this.grid_index('qygl', 'zzvw_gys', '供应商管理');
	}
	
	this.kh_index = function(){
		return this.grid_index('qygl', 'zzvw_kh', '客户管理');
	}
	
	this.wz_index = function(){
		return this.grid_index('qygl', 'wz', '物资管理');
	}
	
	this.wz_fl_index = function(){
		return this.grid_index('qygl', 'wz_fl', '物资分类管理');
	}
	
	this.ck_fl_index = function(){
		return this.grid_index('qygl', 'ck_fl', '仓库分类管理');
	}
	
	this.ck_index = function(){
		return this.grid_index('qygl', 'ck', '仓库管理');
	}
	
	this.hobby_index = function(){
		return this.grid_index('qygl', 'hobby', '爱好管理');
	}
	
	this.ck_weizhi_index = function(){
		return this.grid_index('qygl', 'ck_weizhi', '仓位管理'); //仓库位置
	}
	
	this.zl_index = function(){
		return this.grid_index('qygl', 'zl', '质量管理');
	}
	
	this.defect_index = function(){
		return this.grid_index('qygl', 'defect', '缺陷管理');
	}
	
	this.pici_index = function(){
		return this.grid_index('qygl', 'pici', '批次管理');
	}
	
	this.hb_index = function(){
		return this.grid_index('qygl', 'hb', '伙伴管理');
	}
	
	this.skill_index = function(){
		return this.grid_index('qygl', 'skill', '技能管理');
	}
	
	this.skill_grade_index = function(){
		return this.grid_index('qygl', 'skill_grade', '技能等级管理');
	}
	
	this.credit_level_index = function(){
		return this.grid_index('qygl', 'credit_level', '信用等级管理');
	}
	
	this.dept_index = function(){
		return this.grid_index('qygl', 'dept', '部门管理');
	}
	
	this.position_index = function(){
		return this.grid_index('qygl', 'position', '职位管理');
	}
	
	this.hb_fl_index = function(){
		return this.grid_index('qygl', 'hb_fl', '伙伴类型管理');
	}
	
	this.work_type_index = function(){
		return this.grid_index('qygl', 'work_type', '工种管理');
	}
	
	this.contact_method_index = function(){
		return this.grid_index('qygl', 'contact_method', '联系方式管理');
	}
	
	this.yw_index = function(){
		return this.grid_index('qygl', 'yw', '业务管理');
	}
	
	this.yw_cg_index = function(){
		return this.grid_index('qygl', 'zzvw_yw_xd', '下采购单');
	}
	
	this.yw_sh_index = function(){
		return this.grid_index('qygl', 'zzvw_yw_sh', '收货/退货');
		return this.grid_index('qygl', 'yw_jh', '收货/退货');
	}
	
	this.yw_xs_index = function(){
		return this.grid_index('qygl', 'zzvw_yw_jd', '接订单');
	}
	
	this.yw_fh_index = function(){
		return this.grid_index('qygl', 'zzvw_yw_fh', '发货/收退货');
	}
	
	this.yw_jth_index = function(){
		return this.grid_index('qygl', 'yw_jth', '收退货');
	}
	
	this.yw_chuku_index = function(){
		return this.grid_index('qygl', 'yw_chuku', '出库');
	}
	
	this.dingdan_index = function(){
		return this.grid_index('qygl', 'dingdan', '订单管理');
	}
	
	this.yw_fl_index = function(){
		return this.grid_index('qygl', 'yw_fl', '业务分类管理');
	}
	
	this.fh_fl_index = function(){
		return this.grid_index('qygl', 'fh_fl', '发货方式管理');
	}
	
	this.jszb_index = function(){
		return this.grid_index('qygl', 'jszb', '技术指标管理');
	}
	
	this.unit_fl_index = function(){
		return this.grid_index('qygl', 'unit_fl', '单位分类管理');
	}
	
	this.unit_index = function(){
		return this.grid_index('qygl', 'unit', '计量单位管理');
	}
	
	this.yw_zj_jinchu_index = function(){
		return this.grid_index('qygl', 'zzvw_yw_zj_jinchu', '资金变动');
	}
	
	this.zjzh_index = function(){
		return this.grid_index('qygl', 'zjzh', '资金账户管理');
	}
	
	this.zj_fl_index = function(){
		return this.grid_index('qygl', 'zj_fl', '资金类型管理');
	}
	
	this.zj_cause_index = function(){
		return this.grid_index('qygl', 'zj_cause', '资金变动原因管理');
	}
	
	this.zj_pj_index = function(){
		return this.grid_index('qygl', 'zj_pj', '金融票据管理');
	}
	
	this.sc_index = function(){
		return this.grid_index('qygl', 'sc', '生产登记');
	}
	
	this.gx_index = function(){
		return this.grid_index('qygl', 'gx', '工序管理');
	}
	
	this.gx_fl_index = function(){
		return this.grid_index('qygl', 'gx_fl', '工序分类管理');
	}
	
	this.gx_de_index = function(){
		return this.grid_index('qygl', 'gx_de', '定额管理');
	}
	
	
}).apply(XT);
