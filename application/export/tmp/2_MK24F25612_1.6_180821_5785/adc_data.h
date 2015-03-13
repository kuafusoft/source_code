/*Device: MK24F25612
 Version: 1.6
 Description: MK24F25612 Freescale Microcontroller
*/


#include "../chip/chip.h"
#include "../inc/logic.h"


struct DATA ADC_REG_DATA[] = {
	{OFFSET(FTM_MemMap,PWMLOAD[0]), 152},
	{sizeof(struct FTM_MemMap), 156}
};

struct DATA ADC_BITFIELD_DATA[] = {
	{"FTM_PWMLOAD_LDOK_MASK",FTM_PWMLOAD_LDOK_MASK, MASK(9,1)},
	{"FTM_PWMLOAD_LDOK_SHIFT",FTM_PWMLOAD_LDOK_SHIFT, SHIFT(9)}
};