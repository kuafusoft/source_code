/*Device: MKM34Z5
 Version: 1.6
 Description: MKM34Z5 Freescale Microcontroller
*/


#include "../chip/chip.h"
#include "../inc/logic.h"


struct DATA ADC_REG_DATA[] = {
	{"OFFSET(_MemMap, C2)", OFFSET(_MemMap,C2), 3},
	{"OFFSET(_MemMap, S1)", OFFSET(_MemMap,S1), 4},
	{"OFFSET(_MemMap, S2)", OFFSET(_MemMap,S2), 5},
	{"OFFSET(_MemMap, C3)", OFFSET(_MemMap,C3), 6},
	{"OFFSET(_MemMap, BDH)", OFFSET(_MemMap,BDH), 0},
	{"OFFSET(_MemMap, BDL)", OFFSET(_MemMap,BDL), 1},
	{"OFFSET(_MemMap, D)", OFFSET(_MemMap,D), 7},
	{"OFFSET(_MemMap, MA1)", OFFSET(_MemMap,MA1), 8},
	{"OFFSET(_MemMap, MA2)", OFFSET(_MemMap,MA2), 9},
	{"OFFSET(_MemMap, C4)", OFFSET(_MemMap,C4), 10},
	{"OFFSET(_MemMap, C5)", OFFSET(_MemMap,C5), 11},
	{"OFFSET(_MemMap, ED)", OFFSET(_MemMap,ED), 12},
	{"OFFSET(_MemMap, MODEM)", OFFSET(_MemMap,MODEM), 13},
	{"OFFSET(_MemMap, PFIFO)", OFFSET(_MemMap,PFIFO), 16},
	{"OFFSET(_MemMap, CFIFO)", OFFSET(_MemMap,CFIFO), 17},
	{"OFFSET(_MemMap, SFIFO)", OFFSET(_MemMap,SFIFO), 18},
	{"OFFSET(_MemMap, TWFIFO)", OFFSET(_MemMap,TWFIFO), 19},
	{"OFFSET(_MemMap, TCFIFO)", OFFSET(_MemMap,TCFIFO), 20},
	{"OFFSET(_MemMap, RWFIFO)", OFFSET(_MemMap,RWFIFO), 21},
	{"OFFSET(_MemMap, RCFIFO)", OFFSET(_MemMap,RCFIFO), 22},
	{"OFFSET(_MemMap, C7816)", OFFSET(_MemMap,C7816), 24},
	{"OFFSET(_MemMap, IE7816)", OFFSET(_MemMap,IE7816), 25},
	{"OFFSET(_MemMap, IS7816)", OFFSET(_MemMap,IS7816), 26},
	{"OFFSET(_MemMap, WN7816)", OFFSET(_MemMap,WN7816), 28},
	{"OFFSET(_MemMap, WF7816)", OFFSET(_MemMap,WF7816), 29},
	{"OFFSET(_MemMap, ET7816)", OFFSET(_MemMap,ET7816), 30},
	{"OFFSET(_MemMap, TL7816)", OFFSET(_MemMap,TL7816), 31},
	{"OFFSET(_MemMap, WP7816T0)", OFFSET(_MemMap,WP7816T0), 27},
	{"OFFSET(_MemMap, WP7816T1)", OFFSET(_MemMap,WP7816T1), 27},
	{"sizeof(_MemMap)", sizeof(struct _MemMap), 0}
};

struct DATA ADC_BITFIELD_DATA[] = {
	{"_C1_PT_MASK", _C1_PT_MASK, MASK(0,1)},
	{"_C1_PT_SHIFT", _C1_PT_SHIFT, SHIFT(0)},
	{"_C1_PE_MASK", _C1_PE_MASK, MASK(1,1)},
	{"_C1_PE_SHIFT", _C1_PE_SHIFT, SHIFT(1)},
	{"_C1_ILT_MASK", _C1_ILT_MASK, MASK(2,1)},
	{"_C1_ILT_SHIFT", _C1_ILT_SHIFT, SHIFT(2)},
	{"_C1_WAKE_MASK", _C1_WAKE_MASK, MASK(3,1)},
	{"_C1_WAKE_SHIFT", _C1_WAKE_SHIFT, SHIFT(3)},
	{"_C1_M_MASK", _C1_M_MASK, MASK(4,1)},
	{"_C1_M_SHIFT", _C1_M_SHIFT, SHIFT(4)},
	{"_C1_RSRC_MASK", _C1_RSRC_MASK, MASK(5,1)},
	{"_C1_RSRC_SHIFT", _C1_RSRC_SHIFT, SHIFT(5)},
	{"_C1_LOOPS_MASK", _C1_LOOPS_MASK, MASK(7,1)},
	{"_C1_LOOPS_SHIFT", _C1_LOOPS_SHIFT, SHIFT(7)},
	{"_C2_SBK_MASK", _C2_SBK_MASK, MASK(0,1)},
	{"_C2_SBK_SHIFT", _C2_SBK_SHIFT, SHIFT(0)},
	{"_C2_RWU_MASK", _C2_RWU_MASK, MASK(1,1)},
	{"_C2_RWU_SHIFT", _C2_RWU_SHIFT, SHIFT(1)},
	{"_C2_RE_MASK", _C2_RE_MASK, MASK(2,1)},
	{"_C2_RE_SHIFT", _C2_RE_SHIFT, SHIFT(2)},
	{"_C2_TE_MASK", _C2_TE_MASK, MASK(3,1)},
	{"_C2_TE_SHIFT", _C2_TE_SHIFT, SHIFT(3)},
	{"_S1_PF_MASK", _S1_PF_MASK, MASK(0,1)},
	{"_S1_PF_SHIFT", _S1_PF_SHIFT, SHIFT(0)},
	{"_S1_FE_MASK", _S1_FE_MASK, MASK(1,1)},
	{"_S1_FE_SHIFT", _S1_FE_SHIFT, SHIFT(1)},
	{"_S1_NF_MASK", _S1_NF_MASK, MASK(2,1)},
	{"_S1_NF_SHIFT", _S1_NF_SHIFT, SHIFT(2)},
	{"_S1_OR_MASK", _S1_OR_MASK, MASK(3,1)},
	{"_S1_OR_SHIFT", _S1_OR_SHIFT, SHIFT(3)},
	{"_S1_IDLE_MASK", _S1_IDLE_MASK, MASK(4,1)},
	{"_S1_IDLE_SHIFT", _S1_IDLE_SHIFT, SHIFT(4)},
	{"_S1_RDRF_MASK", _S1_RDRF_MASK, MASK(5,1)},
	{"_S1_RDRF_SHIFT", _S1_RDRF_SHIFT, SHIFT(5)},
	{"_S1_TDRE_MASK", _S1_TDRE_MASK, MASK(7,1)},
	{"_S1_TDRE_SHIFT", _S1_TDRE_SHIFT, SHIFT(7)},
	{"_S2_RAF_MASK", _S2_RAF_MASK, MASK(0,1)},
	{"_S2_RAF_SHIFT", _S2_RAF_SHIFT, SHIFT(0)},
	{"_S2_RXINV_MASK", _S2_RXINV_MASK, MASK(4,1)},
	{"_S2_RXINV_SHIFT", _S2_RXINV_SHIFT, SHIFT(4)},
	{"_S2_RXEDGIF_MASK", _S2_RXEDGIF_MASK, MASK(6,1)},
	{"_S2_RXEDGIF_SHIFT", _S2_RXEDGIF_SHIFT, SHIFT(6)},
	{"_C3_PEIE_MASK", _C3_PEIE_MASK, MASK(0,1)},
	{"_C3_PEIE_SHIFT", _C3_PEIE_SHIFT, SHIFT(0)},
	{"_C3_FEIE_MASK", _C3_FEIE_MASK, MASK(1,1)},
	{"_C3_FEIE_SHIFT", _C3_FEIE_SHIFT, SHIFT(1)},
	{"_C3_NEIE_MASK", _C3_NEIE_MASK, MASK(2,1)},
	{"_C3_NEIE_SHIFT", _C3_NEIE_SHIFT, SHIFT(2)},
	{"_BDH_SBR_MASK", _BDH_SBR_MASK, MASK(0,5)},
	{"_BDH_SBR_SHIFT", _BDH_SBR_SHIFT, SHIFT(0)},
	{"_BDH_SBR_VALUE", _BDH_SBR(1), SHIFT_VALUE(0)},
	{"_BDH_RXEDGIE_MASK", _BDH_RXEDGIE_MASK, MASK(6,1)},
	{"_BDH_RXEDGIE_SHIFT", _BDH_RXEDGIE_SHIFT, SHIFT(6)},
	{"_BDL_SBR_MASK", _BDL_SBR_MASK, MASK(0,8)},
	{"_BDL_SBR_SHIFT", _BDL_SBR_SHIFT, SHIFT(0)},
	{"_BDL_SBR_VALUE", _BDL_SBR(1), SHIFT_VALUE(0)},
	{"_C2_ILIE_MASK", _C2_ILIE_MASK, MASK(4,1)},
	{"_C2_ILIE_SHIFT", _C2_ILIE_SHIFT, SHIFT(4)},
	{"_C2_RIE_MASK", _C2_RIE_MASK, MASK(5,1)},
	{"_C2_RIE_SHIFT", _C2_RIE_SHIFT, SHIFT(5)},
	{"_C2_TCIE_MASK", _C2_TCIE_MASK, MASK(6,1)},
	{"_C2_TCIE_SHIFT", _C2_TCIE_SHIFT, SHIFT(6)},
	{"_C2_TIE_MASK", _C2_TIE_MASK, MASK(7,1)},
	{"_C2_TIE_SHIFT", _C2_TIE_SHIFT, SHIFT(7)},
	{"_S1_TC_MASK", _S1_TC_MASK, MASK(6,1)},
	{"_S1_TC_SHIFT", _S1_TC_SHIFT, SHIFT(6)},
	{"_S2_BRK13_MASK", _S2_BRK13_MASK, MASK(2,1)},
	{"_S2_BRK13_SHIFT", _S2_BRK13_SHIFT, SHIFT(2)},
	{"_S2_RWUID_MASK", _S2_RWUID_MASK, MASK(3,1)},
	{"_S2_RWUID_SHIFT", _S2_RWUID_SHIFT, SHIFT(3)},
	{"_S2_MSBF_MASK", _S2_MSBF_MASK, MASK(5,1)},
	{"_S2_MSBF_SHIFT", _S2_MSBF_SHIFT, SHIFT(5)},
	{"_C3_ORIE_MASK", _C3_ORIE_MASK, MASK(3,1)},
	{"_C3_ORIE_SHIFT", _C3_ORIE_SHIFT, SHIFT(3)},
	{"_C3_TXINV_MASK", _C3_TXINV_MASK, MASK(4,1)},
	{"_C3_TXINV_SHIFT", _C3_TXINV_SHIFT, SHIFT(4)},
	{"_C3_TXDIR_MASK", _C3_TXDIR_MASK, MASK(5,1)},
	{"_C3_TXDIR_SHIFT", _C3_TXDIR_SHIFT, SHIFT(5)},
	{"_C3_T8_MASK", _C3_T8_MASK, MASK(6,1)},
	{"_C3_T8_SHIFT", _C3_T8_SHIFT, SHIFT(6)},
	{"_C3_R8_MASK", _C3_R8_MASK, MASK(7,1)},
	{"_C3_R8_SHIFT", _C3_R8_SHIFT, SHIFT(7)},
	{"_D_RT_MASK", _D_RT_MASK, MASK(0,8)},
	{"_D_RT_SHIFT", _D_RT_SHIFT, SHIFT(0)},
	{"_D_RT_VALUE", _D_RT(1), SHIFT_VALUE(0)},
	{"_MA1_MA_MASK", _MA1_MA_MASK, MASK(0,8)},
	{"_MA1_MA_SHIFT", _MA1_MA_SHIFT, SHIFT(0)},
	{"_MA1_MA_VALUE", _MA1_MA(1), SHIFT_VALUE(0)},
	{"_MA2_MA_MASK", _MA2_MA_MASK, MASK(0,8)},
	{"_MA2_MA_SHIFT", _MA2_MA_SHIFT, SHIFT(0)},
	{"_MA2_MA_VALUE", _MA2_MA(1), SHIFT_VALUE(0)},
	{"_C4_BRFA_MASK", _C4_BRFA_MASK, MASK(0,5)},
	{"_C4_BRFA_SHIFT", _C4_BRFA_SHIFT, SHIFT(0)},
	{"_C4_BRFA_VALUE", _C4_BRFA(1), SHIFT_VALUE(0)},
	{"_C4_M10_MASK", _C4_M10_MASK, MASK(5,1)},
	{"_C4_M10_SHIFT", _C4_M10_SHIFT, SHIFT(5)},
	{"_C4_MAEN2_MASK", _C4_MAEN2_MASK, MASK(6,1)},
	{"_C4_MAEN2_SHIFT", _C4_MAEN2_SHIFT, SHIFT(6)},
	{"_C4_MAEN1_MASK", _C4_MAEN1_MASK, MASK(7,1)},
	{"_C4_MAEN1_SHIFT", _C4_MAEN1_SHIFT, SHIFT(7)},
	{"_C5_RDMAS_MASK", _C5_RDMAS_MASK, MASK(5,1)},
	{"_C5_RDMAS_SHIFT", _C5_RDMAS_SHIFT, SHIFT(5)},
	{"_C5_TDMAS_MASK", _C5_TDMAS_MASK, MASK(7,1)},
	{"_C5_TDMAS_SHIFT", _C5_TDMAS_SHIFT, SHIFT(7)},
	{"_ED_PARITYE_MASK", _ED_PARITYE_MASK, MASK(6,1)},
	{"_ED_PARITYE_SHIFT", _ED_PARITYE_SHIFT, SHIFT(6)},
	{"_ED_NOISY_MASK", _ED_NOISY_MASK, MASK(7,1)},
	{"_ED_NOISY_SHIFT", _ED_NOISY_SHIFT, SHIFT(7)},
	{"_MODEM_TXCTSE_MASK", _MODEM_TXCTSE_MASK, MASK(0,1)},
	{"_MODEM_TXCTSE_SHIFT", _MODEM_TXCTSE_SHIFT, SHIFT(0)},
	{"_MODEM_TXRTSE_MASK", _MODEM_TXRTSE_MASK, MASK(1,1)},
	{"_MODEM_TXRTSE_SHIFT", _MODEM_TXRTSE_SHIFT, SHIFT(1)},
	{"_MODEM_TXRTSPOL_MASK", _MODEM_TXRTSPOL_MASK, MASK(2,1)},
	{"_MODEM_TXRTSPOL_SHIFT", _MODEM_TXRTSPOL_SHIFT, SHIFT(2)},
	{"_MODEM_RXRTSE_MASK", _MODEM_RXRTSE_MASK, MASK(3,1)},
	{"_MODEM_RXRTSE_SHIFT", _MODEM_RXRTSE_SHIFT, SHIFT(3)},
	{"_PFIFO_RXFIFOSIZE_MASK", _PFIFO_RXFIFOSIZE_MASK, MASK(0,3)},
	{"_PFIFO_RXFIFOSIZE_SHIFT", _PFIFO_RXFIFOSIZE_SHIFT, SHIFT(0)},
	{"_PFIFO_RXFIFOSIZE_VALUE", _PFIFO_RXFIFOSIZE(1), SHIFT_VALUE(0)},
	{"_PFIFO_RXFE_MASK", _PFIFO_RXFE_MASK, MASK(3,1)},
	{"_PFIFO_RXFE_SHIFT", _PFIFO_RXFE_SHIFT, SHIFT(3)},
	{"_PFIFO_TXFIFOSIZE_MASK", _PFIFO_TXFIFOSIZE_MASK, MASK(4,3)},
	{"_PFIFO_TXFIFOSIZE_SHIFT", _PFIFO_TXFIFOSIZE_SHIFT, SHIFT(4)},
	{"_PFIFO_TXFIFOSIZE_VALUE", _PFIFO_TXFIFOSIZE(1), SHIFT_VALUE(4)},
	{"_PFIFO_TXFE_MASK", _PFIFO_TXFE_MASK, MASK(7,1)},
	{"_PFIFO_TXFE_SHIFT", _PFIFO_TXFE_SHIFT, SHIFT(7)},
	{"_CFIFO_RXUFE_MASK", _CFIFO_RXUFE_MASK, MASK(0,1)},
	{"_CFIFO_RXUFE_SHIFT", _CFIFO_RXUFE_SHIFT, SHIFT(0)},
	{"_CFIFO_TXOFE_MASK", _CFIFO_TXOFE_MASK, MASK(1,1)},
	{"_CFIFO_TXOFE_SHIFT", _CFIFO_TXOFE_SHIFT, SHIFT(1)},
	{"_CFIFO_RXOFE_MASK", _CFIFO_RXOFE_MASK, MASK(2,1)},
	{"_CFIFO_RXOFE_SHIFT", _CFIFO_RXOFE_SHIFT, SHIFT(2)},
	{"_CFIFO_RXFLUSH_MASK", _CFIFO_RXFLUSH_MASK, MASK(6,1)},
	{"_CFIFO_RXFLUSH_SHIFT", _CFIFO_RXFLUSH_SHIFT, SHIFT(6)},
	{"_CFIFO_TXFLUSH_MASK", _CFIFO_TXFLUSH_MASK, MASK(7,1)},
	{"_CFIFO_TXFLUSH_SHIFT", _CFIFO_TXFLUSH_SHIFT, SHIFT(7)},
	{"_SFIFO_RXUF_MASK", _SFIFO_RXUF_MASK, MASK(0,1)},
	{"_SFIFO_RXUF_SHIFT", _SFIFO_RXUF_SHIFT, SHIFT(0)},
	{"_SFIFO_TXOF_MASK", _SFIFO_TXOF_MASK, MASK(1,1)},
	{"_SFIFO_TXOF_SHIFT", _SFIFO_TXOF_SHIFT, SHIFT(1)},
	{"_SFIFO_RXOF_MASK", _SFIFO_RXOF_MASK, MASK(2,1)},
	{"_SFIFO_RXOF_SHIFT", _SFIFO_RXOF_SHIFT, SHIFT(2)},
	{"_SFIFO_RXEMPT_MASK", _SFIFO_RXEMPT_MASK, MASK(6,1)},
	{"_SFIFO_RXEMPT_SHIFT", _SFIFO_RXEMPT_SHIFT, SHIFT(6)},
	{"_SFIFO_TXEMPT_MASK", _SFIFO_TXEMPT_MASK, MASK(7,1)},
	{"_SFIFO_TXEMPT_SHIFT", _SFIFO_TXEMPT_SHIFT, SHIFT(7)},
	{"_TWFIFO_TXWATER_MASK", _TWFIFO_TXWATER_MASK, MASK(0,8)},
	{"_TWFIFO_TXWATER_SHIFT", _TWFIFO_TXWATER_SHIFT, SHIFT(0)},
	{"_TWFIFO_TXWATER_VALUE", _TWFIFO_TXWATER(1), SHIFT_VALUE(0)},
	{"_TCFIFO_TXCOUNT_MASK", _TCFIFO_TXCOUNT_MASK, MASK(0,8)},
	{"_TCFIFO_TXCOUNT_SHIFT", _TCFIFO_TXCOUNT_SHIFT, SHIFT(0)},
	{"_TCFIFO_TXCOUNT_VALUE", _TCFIFO_TXCOUNT(1), SHIFT_VALUE(0)},
	{"_RWFIFO_RXWATER_MASK", _RWFIFO_RXWATER_MASK, MASK(0,8)},
	{"_RWFIFO_RXWATER_SHIFT", _RWFIFO_RXWATER_SHIFT, SHIFT(0)},
	{"_RWFIFO_RXWATER_VALUE", _RWFIFO_RXWATER(1), SHIFT_VALUE(0)},
	{"_RCFIFO_RXCOUNT_MASK", _RCFIFO_RXCOUNT_MASK, MASK(0,8)},
	{"_RCFIFO_RXCOUNT_SHIFT", _RCFIFO_RXCOUNT_SHIFT, SHIFT(0)},
	{"_RCFIFO_RXCOUNT_VALUE", _RCFIFO_RXCOUNT(1), SHIFT_VALUE(0)},
	{"_C7816_ISO_7816E_MASK", _C7816_ISO_7816E_MASK, MASK(0,1)},
	{"_C7816_ISO_7816E_SHIFT", _C7816_ISO_7816E_SHIFT, SHIFT(0)},
	{"_C7816_TTYPE_MASK", _C7816_TTYPE_MASK, MASK(1,1)},
	{"_C7816_TTYPE_SHIFT", _C7816_TTYPE_SHIFT, SHIFT(1)},
	{"_C7816_INIT_MASK", _C7816_INIT_MASK, MASK(2,1)},
	{"_C7816_INIT_SHIFT", _C7816_INIT_SHIFT, SHIFT(2)},
	{"_C7816_ANACK_MASK", _C7816_ANACK_MASK, MASK(3,1)},
	{"_C7816_ANACK_SHIFT", _C7816_ANACK_SHIFT, SHIFT(3)},
	{"_C7816_ONACK_MASK", _C7816_ONACK_MASK, MASK(4,1)},
	{"_C7816_ONACK_SHIFT", _C7816_ONACK_SHIFT, SHIFT(4)},
	{"_IE7816_RXTE_MASK", _IE7816_RXTE_MASK, MASK(0,1)},
	{"_IE7816_RXTE_SHIFT", _IE7816_RXTE_SHIFT, SHIFT(0)},
	{"_IE7816_TXTE_MASK", _IE7816_TXTE_MASK, MASK(1,1)},
	{"_IE7816_TXTE_SHIFT", _IE7816_TXTE_SHIFT, SHIFT(1)},
	{"_IE7816_GTVE_MASK", _IE7816_GTVE_MASK, MASK(2,1)},
	{"_IE7816_GTVE_SHIFT", _IE7816_GTVE_SHIFT, SHIFT(2)},
	{"_IE7816_INITDE_MASK", _IE7816_INITDE_MASK, MASK(4,1)},
	{"_IE7816_INITDE_SHIFT", _IE7816_INITDE_SHIFT, SHIFT(4)},
	{"_IE7816_BWTE_MASK", _IE7816_BWTE_MASK, MASK(5,1)},
	{"_IE7816_BWTE_SHIFT", _IE7816_BWTE_SHIFT, SHIFT(5)},
	{"_IE7816_CWTE_MASK", _IE7816_CWTE_MASK, MASK(6,1)},
	{"_IE7816_CWTE_SHIFT", _IE7816_CWTE_SHIFT, SHIFT(6)},
	{"_IE7816_WTE_MASK", _IE7816_WTE_MASK, MASK(7,1)},
	{"_IE7816_WTE_SHIFT", _IE7816_WTE_SHIFT, SHIFT(7)},
	{"_IS7816_RXT_MASK", _IS7816_RXT_MASK, MASK(0,1)},
	{"_IS7816_RXT_SHIFT", _IS7816_RXT_SHIFT, SHIFT(0)},
	{"_IS7816_TXT_MASK", _IS7816_TXT_MASK, MASK(1,1)},
	{"_IS7816_TXT_SHIFT", _IS7816_TXT_SHIFT, SHIFT(1)},
	{"_IS7816_GTV_MASK", _IS7816_GTV_MASK, MASK(2,1)},
	{"_IS7816_GTV_SHIFT", _IS7816_GTV_SHIFT, SHIFT(2)},
	{"_IS7816_INITD_MASK", _IS7816_INITD_MASK, MASK(4,1)},
	{"_IS7816_INITD_SHIFT", _IS7816_INITD_SHIFT, SHIFT(4)},
	{"_IS7816_BWT_MASK", _IS7816_BWT_MASK, MASK(5,1)},
	{"_IS7816_BWT_SHIFT", _IS7816_BWT_SHIFT, SHIFT(5)},
	{"_IS7816_CWT_MASK", _IS7816_CWT_MASK, MASK(6,1)},
	{"_IS7816_CWT_SHIFT", _IS7816_CWT_SHIFT, SHIFT(6)},
	{"_IS7816_WT_MASK", _IS7816_WT_MASK, MASK(7,1)},
	{"_IS7816_WT_SHIFT", _IS7816_WT_SHIFT, SHIFT(7)},
	{"_WN7816_GTN_MASK", _WN7816_GTN_MASK, MASK(0,8)},
	{"_WN7816_GTN_SHIFT", _WN7816_GTN_SHIFT, SHIFT(0)},
	{"_WN7816_GTN_VALUE", _WN7816_GTN(1), SHIFT_VALUE(0)},
	{"_WF7816_GTFD_MASK", _WF7816_GTFD_MASK, MASK(0,8)},
	{"_WF7816_GTFD_SHIFT", _WF7816_GTFD_SHIFT, SHIFT(0)},
	{"_WF7816_GTFD_VALUE", _WF7816_GTFD(1), SHIFT_VALUE(0)},
	{"_ET7816_RXTHRESHOLD_MASK", _ET7816_RXTHRESHOLD_MASK, MASK(0,4)},
	{"_ET7816_RXTHRESHOLD_SHIFT", _ET7816_RXTHRESHOLD_SHIFT, SHIFT(0)},
	{"_ET7816_RXTHRESHOLD_VALUE", _ET7816_RXTHRESHOLD(1), SHIFT_VALUE(0)},
	{"_ET7816_TXTHRESHOLD_MASK", _ET7816_TXTHRESHOLD_MASK, MASK(4,4)},
	{"_ET7816_TXTHRESHOLD_SHIFT", _ET7816_TXTHRESHOLD_SHIFT, SHIFT(4)},
	{"_ET7816_TXTHRESHOLD_VALUE", _ET7816_TXTHRESHOLD(1), SHIFT_VALUE(4)},
	{"_TL7816_TLEN_MASK", _TL7816_TLEN_MASK, MASK(0,8)},
	{"_TL7816_TLEN_SHIFT", _TL7816_TLEN_SHIFT, SHIFT(0)},
	{"_TL7816_TLEN_VALUE", _TL7816_TLEN(1), SHIFT_VALUE(0)},
	{"_WP7816T0_WI_MASK", _WP7816T0_WI_MASK, MASK(0,8)},
	{"_WP7816T0_WI_SHIFT", _WP7816T0_WI_SHIFT, SHIFT(0)},
	{"_WP7816T0_WI_VALUE", _WP7816T0_WI(1), SHIFT_VALUE(0)},
	{"_WP7816T1_BWI_MASK", _WP7816T1_BWI_MASK, MASK(0,4)},
	{"_WP7816T1_BWI_SHIFT", _WP7816T1_BWI_SHIFT, SHIFT(0)},
	{"_WP7816T1_BWI_VALUE", _WP7816T1_BWI(1), SHIFT_VALUE(0)},
	{"_WP7816T1_CWI_MASK", _WP7816T1_CWI_MASK, MASK(4,4)},
	{"_WP7816T1_CWI_SHIFT", _WP7816T1_CWI_SHIFT, SHIFT(4)},
	{"_WP7816T1_CWI_VALUE", _WP7816T1_CWI(1), SHIFT_VALUE(4)}
};