/*Device: MK24F25612
 Version: 1.6
 Description: MK24F25612 Freescale Microcontroller
*/


#include "../chip/chip.h"
#include "../inc/logic.h"


/*THIS FILE INCLUDE THE FTFA_FlashConfig DATA */

struct DATA FTFA_FlashConfig_REG_DATA[] = {
	{OFFSET(FTFA_FlashConfig_MemMap,BACKKEY3), 0},
	{OFFSET(FTFA_FlashConfig_MemMap,FPROT3), 8},
	{OFFSET(FTFA_FlashConfig_MemMap,FSEC), 12},
	{OFFSET(FTFA_FlashConfig_MemMap,FOPT), 13},
	{sizeof(struct FTFA_FlashConfig_MemMap), 14}
};


struct DATA FTFA_FlashConfig_BITFIELD_DATA[] = {
	{FTFA_FlashConfig_BACKKEY3_KEY_MASK, MASK(0,8)},
	{FTFA_FlashConfig_BACKKEY3_KEY_SHIFT, SHIFT(0)},
	{FTFA_FlashConfig_BACKKEY3_KEY(1), SHIFT_VALUE(0)}
,
	{FTFA_FlashConfig_FPROT3_PROT_MASK, MASK(0,8)},
	{FTFA_FlashConfig_FPROT3_PROT_SHIFT, SHIFT(0)},
	{FTFA_FlashConfig_FPROT3_PROT(1), SHIFT_VALUE(0)}
,
	{FTFA_FlashConfig_FSEC_SEC_MASK, MASK(0,2)},
	{FTFA_FlashConfig_FSEC_SEC_SHIFT, SHIFT(0)},
	{FTFA_FlashConfig_FSEC_SEC(1), SHIFT_VALUE(0)}
,
	{FTFA_FlashConfig_FSEC_FSLACC_MASK, MASK(2,2)},
	{FTFA_FlashConfig_FSEC_FSLACC_SHIFT, SHIFT(2)},
	{FTFA_FlashConfig_FSEC_FSLACC(1), SHIFT_VALUE(2)}
,
	{FTFA_FlashConfig_FSEC_MEEN_MASK, MASK(4,2)},
	{FTFA_FlashConfig_FSEC_MEEN_SHIFT, SHIFT(4)},
	{FTFA_FlashConfig_FSEC_MEEN(1), SHIFT_VALUE(4)}
,
	{FTFA_FlashConfig_FSEC_KEYEN_MASK, MASK(6,2)},
	{FTFA_FlashConfig_FSEC_KEYEN_SHIFT, SHIFT(6)},
	{FTFA_FlashConfig_FSEC_KEYEN(1), SHIFT_VALUE(6)}
,
	{FTFA_FlashConfig_FOPT_LPBOOT_MASK, MASK(0,1)},
	{FTFA_FlashConfig_FOPT_LPBOOT_SHIFT, SHIFT(0)},
	{FTFA_FlashConfig_FOPT_EZPORT_DIS_MASK, MASK(1,1)},
	{FTFA_FlashConfig_FOPT_EZPORT_DIS_SHIFT, SHIFT(1)},
	{FTFA_FlashConfig_FOPT_NMI_DIS_MASK, MASK(2,1)},
	{FTFA_FlashConfig_FOPT_NMI_DIS_SHIFT, SHIFT(2)},
	{FTFA_FlashConfig_FOPT_FAST_INIT_MASK, MASK(5,1)},
	{FTFA_FlashConfig_FOPT_FAST_INIT_SHIFT, SHIFT(5)}
};