/*Device: MK24F25612
 Version: 1.6
 Description: MK24F25612 Freescale Microcontroller
*/


#include "../chip/chip.h"
#include "../inc/logic.h"


struct DATA ADC_REG_DATA[] = {
	{OFFSET(DMA_MemMap,DCHPRI12[0]), 271},
	{sizeof(struct DMA_MemMap), 4608}
};

struct DATA ADC_BITFIELD_DATA[] = {
	{"DMA_BITER_ELINKNO_BITER_MASK",DMA_BITER_ELINKNO_BITER_MASK, MASK(0,15)},
	{"DMA_BITER_ELINKNO_BITER_SHIFT",DMA_BITER_ELINKNO_BITER_SHIFT, SHIFT(0)},
	{"DMA_BITER_ELINKNO_BITER_VALUE",DMA_BITER_ELINKNO_BITER(1), SHIFT_VALUE(0)}
};