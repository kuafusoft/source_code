/*Device: MK24F25612
 Version: 1.6
 Description: MK24F25612 Freescale Microcontroller
*/


#include "../chip/chip.h"
#include "../inc/logic.h"


/*THIS FILE INCLUDE THE FMC DATA */

struct DATA FMC_REG_DATA[] = {
	{OFFSET(FMC_MemMap,PFAPR), 0},
	{OFFSET(FMC_MemMap,PFB0CR), 4},
	{OFFSET(FMC_MemMap,TAGVDW0S[0]), 256},
	{OFFSET(FMC_MemMap,TAGVDW1S[0]), 272},
	{OFFSET(FMC_MemMap,TAGVDW2S[0]), 288},
	{OFFSET(FMC_MemMap,TAGVDW3S[0]), 304},
	{OFFSET(FMC_MemMap,DATA_UM[0]), 512},
	{OFFSET(FMC_MemMap,DATA_MU[0]), 516},
	{OFFSET(FMC_MemMap,DATA_ML[0]), 520},
	{OFFSET(FMC_MemMap,DATA_LM[0]), 524},
	{sizeof(struct FMC_MemMap), 768}
};


struct DATA FMC_BITFIELD_DATA[] = {
	{FMC_PFAPR_M0AP_MASK, MASK(0,2)},
	{FMC_PFAPR_M0AP_SHIFT, SHIFT(0)},
	{FMC_PFAPR_M0AP(1), SHIFT_VALUE(0)}
,
	{FMC_PFAPR_M1AP_MASK, MASK(2,2)},
	{FMC_PFAPR_M1AP_SHIFT, SHIFT(2)},
	{FMC_PFAPR_M1AP(1), SHIFT_VALUE(2)}
,
	{FMC_PFAPR_M2AP_MASK, MASK(4,2)},
	{FMC_PFAPR_M2AP_SHIFT, SHIFT(4)},
	{FMC_PFAPR_M2AP(1), SHIFT_VALUE(4)}
,
	{FMC_PFAPR_M3AP_MASK, MASK(6,2)},
	{FMC_PFAPR_M3AP_SHIFT, SHIFT(6)},
	{FMC_PFAPR_M3AP(1), SHIFT_VALUE(6)}
,
	{FMC_PFAPR_M0PFD_MASK, MASK(16,1)},
	{FMC_PFAPR_M0PFD_SHIFT, SHIFT(16)},
	{FMC_PFAPR_M1PFD_MASK, MASK(17,1)},
	{FMC_PFAPR_M1PFD_SHIFT, SHIFT(17)},
	{FMC_PFAPR_M2PFD_MASK, MASK(18,1)},
	{FMC_PFAPR_M2PFD_SHIFT, SHIFT(18)},
	{FMC_PFAPR_M3PFD_MASK, MASK(19,1)},
	{FMC_PFAPR_M3PFD_SHIFT, SHIFT(19)},
	{FMC_PFB0CR_B0SEBE_MASK, MASK(0,1)},
	{FMC_PFB0CR_B0SEBE_SHIFT, SHIFT(0)},
	{FMC_PFB0CR_B0IPE_MASK, MASK(1,1)},
	{FMC_PFB0CR_B0IPE_SHIFT, SHIFT(1)},
	{FMC_PFB0CR_B0DPE_MASK, MASK(2,1)},
	{FMC_PFB0CR_B0DPE_SHIFT, SHIFT(2)},
	{FMC_PFB0CR_B0ICE_MASK, MASK(3,1)},
	{FMC_PFB0CR_B0ICE_SHIFT, SHIFT(3)},
	{FMC_PFB0CR_B0DCE_MASK, MASK(4,1)},
	{FMC_PFB0CR_B0DCE_SHIFT, SHIFT(4)},
	{FMC_PFB0CR_CRC_MASK, MASK(5,3)},
	{FMC_PFB0CR_CRC_SHIFT, SHIFT(5)},
	{FMC_PFB0CR_CRC(1), SHIFT_VALUE(5)}
,
	{FMC_PFB0CR_B0MW_MASK, MASK(17,2)},
	{FMC_PFB0CR_B0MW_SHIFT, SHIFT(17)},
	{FMC_PFB0CR_B0MW(1), SHIFT_VALUE(17)}
,
	{FMC_PFB0CR_S_B_INV_MASK, MASK(19,1)},
	{FMC_PFB0CR_S_B_INV_SHIFT, SHIFT(19)},
	{FMC_PFB0CR_CINV_WAY_MASK, MASK(20,4)},
	{FMC_PFB0CR_CINV_WAY_SHIFT, SHIFT(20)},
	{FMC_PFB0CR_CINV_WAY(1), SHIFT_VALUE(20)}
,
	{FMC_PFB0CR_CLCK_WAY_MASK, MASK(24,4)},
	{FMC_PFB0CR_CLCK_WAY_SHIFT, SHIFT(24)},
	{FMC_PFB0CR_CLCK_WAY(1), SHIFT_VALUE(24)}
,
	{FMC_PFB0CR_B0RWSC_MASK, MASK(28,4)},
	{FMC_PFB0CR_B0RWSC_SHIFT, SHIFT(28)},
	{FMC_PFB0CR_B0RWSC(1), SHIFT_VALUE(28)}
,
	{FMC_TAGVDW0S_valid_MASK, MASK(0,1)},
	{FMC_TAGVDW0S_valid_SHIFT, SHIFT(0)},
	{FMC_TAGVDW1S_valid_MASK, MASK(0,1)},
	{FMC_TAGVDW1S_valid_SHIFT, SHIFT(0)},
	{FMC_TAGVDW2S_valid_MASK, MASK(0,1)},
	{FMC_TAGVDW2S_valid_SHIFT, SHIFT(0)},
	{FMC_TAGVDW3S_valid_MASK, MASK(0,1)},
	{FMC_TAGVDW3S_valid_SHIFT, SHIFT(0)},
	{FMC_TAGVDW0S_cache_tag_MASK, MASK(6,14)},
	{FMC_TAGVDW0S_cache_tag_SHIFT, SHIFT(6)},
	{FMC_TAGVDW0S_cache_tag(1), SHIFT_VALUE(6)}
,
	{FMC_TAGVDW1S_cache_tag_MASK, MASK(6,14)},
	{FMC_TAGVDW1S_cache_tag_SHIFT, SHIFT(6)},
	{FMC_TAGVDW1S_cache_tag(1), SHIFT_VALUE(6)}
,
	{FMC_TAGVDW2S_cache_tag_MASK, MASK(6,14)},
	{FMC_TAGVDW2S_cache_tag_SHIFT, SHIFT(6)},
	{FMC_TAGVDW2S_cache_tag(1), SHIFT_VALUE(6)}
,
	{FMC_TAGVDW3S_cache_tag_MASK, MASK(6,14)},
	{FMC_TAGVDW3S_cache_tag_SHIFT, SHIFT(6)},
	{FMC_TAGVDW3S_cache_tag(1), SHIFT_VALUE(6)}
,
	{FMC_DATA_UM_DATA_MASK, MASK(0,32)},
	{FMC_DATA_UM_DATA_SHIFT, SHIFT(0)},
	{FMC_DATA_UM_DATA(1), SHIFT_VALUE(0)}
,
	{FMC_DATA_UM_DATA_MASK, MASK(0,32)},
	{FMC_DATA_UM_DATA_SHIFT, SHIFT(0)},
	{FMC_DATA_UM_DATA(1), SHIFT_VALUE(0)}
,
	{FMC_DATA_UM_DATA_MASK, MASK(0,32)},
	{FMC_DATA_UM_DATA_SHIFT, SHIFT(0)},
	{FMC_DATA_UM_DATA(1), SHIFT_VALUE(0)}
,
	{FMC_DATA_UM_DATA_MASK, MASK(0,32)},
	{FMC_DATA_UM_DATA_SHIFT, SHIFT(0)},
	{FMC_DATA_UM_DATA(1), SHIFT_VALUE(0)}
,
	{FMC_DATA_MU_DATA_MASK, MASK(0,32)},
	{FMC_DATA_MU_DATA_SHIFT, SHIFT(0)},
	{FMC_DATA_MU_DATA(1), SHIFT_VALUE(0)}
,
	{FMC_DATA_MU_DATA_MASK, MASK(0,32)},
	{FMC_DATA_MU_DATA_SHIFT, SHIFT(0)},
	{FMC_DATA_MU_DATA(1), SHIFT_VALUE(0)}
,
	{FMC_DATA_MU_DATA_MASK, MASK(0,32)},
	{FMC_DATA_MU_DATA_SHIFT, SHIFT(0)},
	{FMC_DATA_MU_DATA(1), SHIFT_VALUE(0)}
,
	{FMC_DATA_MU_DATA_MASK, MASK(0,32)},
	{FMC_DATA_MU_DATA_SHIFT, SHIFT(0)},
	{FMC_DATA_MU_DATA(1), SHIFT_VALUE(0)}
,
	{FMC_DATA_ML_DATA_MASK, MASK(0,32)},
	{FMC_DATA_ML_DATA_SHIFT, SHIFT(0)},
	{FMC_DATA_ML_DATA(1), SHIFT_VALUE(0)}
,
	{FMC_DATA_ML_DATA_MASK, MASK(0,32)},
	{FMC_DATA_ML_DATA_SHIFT, SHIFT(0)},
	{FMC_DATA_ML_DATA(1), SHIFT_VALUE(0)}
,
	{FMC_DATA_ML_DATA_MASK, MASK(0,32)},
	{FMC_DATA_ML_DATA_SHIFT, SHIFT(0)},
	{FMC_DATA_ML_DATA(1), SHIFT_VALUE(0)}
,
	{FMC_DATA_ML_DATA_MASK, MASK(0,32)},
	{FMC_DATA_ML_DATA_SHIFT, SHIFT(0)},
	{FMC_DATA_ML_DATA(1), SHIFT_VALUE(0)}
,
	{FMC_DATA_LM_DATA_MASK, MASK(0,32)},
	{FMC_DATA_LM_DATA_SHIFT, SHIFT(0)},
	{FMC_DATA_LM_DATA(1), SHIFT_VALUE(0)}
,
	{FMC_DATA_LM_DATA_MASK, MASK(0,32)},
	{FMC_DATA_LM_DATA_SHIFT, SHIFT(0)},
	{FMC_DATA_LM_DATA(1), SHIFT_VALUE(0)}
,
	{FMC_DATA_LM_DATA_MASK, MASK(0,32)},
	{FMC_DATA_LM_DATA_SHIFT, SHIFT(0)},
	{FMC_DATA_LM_DATA(1), SHIFT_VALUE(0)}
,
	{FMC_DATA_LM_DATA_MASK, MASK(0,32)},
	{FMC_DATA_LM_DATA_SHIFT, SHIFT(0)},
	{FMC_DATA_LM_DATA(1), SHIFT_VALUE(0)}

};