/*Device: MK24F25612
 Version: 1.6
 Description: MK24F25612 Freescale Microcontroller
*/


#include "../chip/chip.h"
#include "../inc/logic.h"


struct DATA ADC_REG_DATA[] = {
	{"OFFSET(ADC_MemMap",OFFSET(ADC_MemMap,CLM0[0]), 108},
	{sizeof(struct ADC_MemMap), 112}
};

struct DATA ADC_BITFIELD_DATA[] = {
	{"ADC_CLM0_CLM0_MASK",ADC_CLM0_CLM0_MASK, MASK(0,6)},
	{"ADC_CLM0_CLM0_SHIFT",ADC_CLM0_CLM0_SHIFT, SHIFT(0)},
	{"ADC_CLM0_CLM0_VALUE",ADC_CLM0_CLM0(1), SHIFT_VALUE(0)}
};